<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Services\ProjectLifecycleService;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function __construct(
        protected ProjectLifecycleService $lifecycleService,
        protected WhatsAppService $waService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        // Respond to GET healthcheck
        if ($request->isMethod('get')) {
            return response()->json([
                'status'  => 'ok',
                'message' => 'BuildClient Xendit webhook receiver is active and ready.',
            ], 200);
        }

        // 1. Validasi Autentikasi: Internal Shared Token atau Xendit Callback Token
        $internalToken = $request->header('X-Internal-Token');
        $callbackToken = $request->header('x-callback-token')
            ?? $request->header('X-CALLBACK-TOKEN')
            ?? $request->input('callback_token');

        $expectedInternal = config('services.xendit.internal_secret', 'vexahost_internal_xnd_token_38c92a');
        $expectedCallback = config('services.xendit.webhook_token') ?: env('XENDIT_WEBHOOK_TOKEN');

        $authorized = false;
        if ($internalToken && hash_equals((string) $expectedInternal, (string) $internalToken)) {
            $authorized = true;
        } elseif ($callbackToken && hash_equals((string) $expectedCallback, (string) $callbackToken)) {
            $authorized = true;
        }

        if (! $authorized) {
            Log::warning('BuildClient Xendit webhook unauthorized', [
                'ip'             => $request->ip(),
                'internal_token' => $internalToken ? 'provided' : 'missing',
                'callback_token' => $callbackToken ? 'provided' : 'missing',
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized webhook token or internal secret.',
            ], 403);
        }

        $payload = $request->all();
        $externalId = (string) ($payload['external_id'] ?? '');
        $xenditId = (string) ($payload['id'] ?? '');
        $status = strtoupper((string) ($payload['status'] ?? ''));
        $paidAmount = (float) ($payload['paid_amount'] ?? ($payload['amount'] ?? 0));

        Log::info('BuildClient received Xendit payment event', [
            'external_id' => $externalId,
            'xendit_id'   => $xenditId,
            'status'      => $status,
            'paid_amount' => $paidAmount,
        ]);

        // Tanggapi uji coba / ping
        if (in_array(strtolower($status), ['test', 'ping'], true)) {
            return response()->json(['status' => 'success', 'message' => 'Test ping acknowledged.']);
        }

        // Hanya proses pembayaran sukses (PAID / SETTLED)
        if (! in_array($status, ['PAID', 'SETTLED'], true)) {
            return response()->json(['status' => 'ignored', 'message' => "Status '{$status}' diabaikan."]);
        }

        // Cari invoice berdasarkan metadata atau external_id
        $invoice = null;
        $metaInvoiceId = $payload['metadata']['invoice_id'] ?? null;
        if ($metaInvoiceId) {
            $invoice = Invoice::with(['project.lead', 'client', 'subscription.lead'])->find($metaInvoiceId);
        }

        $metaToken = $payload['metadata']['payment_token'] ?? null;
        if (! $invoice && $metaToken) {
            $invoice = Invoice::where('payment_token', $metaToken)->with(['project.lead', 'client', 'subscription.lead'])->first();
        }

        $detectedType = $payload['metadata']['payment_type'] ?? null;

        if (! $invoice && preg_match('/^BC-INV-(\d+)(?:-(dp|full))?/i', $externalId, $m)) {
            $invoice = Invoice::with(['project.lead', 'client', 'subscription.lead'])->find((int) $m[1]);
            if (! $detectedType && isset($m[2])) {
                $detectedType = strtolower($m[2]);
            }
        }

        if (! $invoice && filled($xenditId)) {
            $invoice = Invoice::where('payment_reference', $xenditId)->with(['project.lead', 'client', 'subscription.lead'])->first();
        }

        if (! $invoice) {
            Log::warning('BuildClient: Invoice tidak ditemukan untuk Xendit event: ' . ($xenditId ?: $externalId));
            return response()->json(['status' => 'not_found', 'message' => 'Invoice tidak ditemukan.'], 200);
        }

        // Idempotency: Jika invoice sudah lunas penuh
        if ($invoice->status === 'paid') {
            return response()->json(['status' => 'already_paid', 'message' => 'Invoice sudah berstatus lunas.']);
        }

        return $this->settleInvoice($invoice, $payload);
    }

    public function settleInvoice(Invoice $invoice, array $payload): JsonResponse
    {
        if ($invoice->status === 'paid') {
            return response()->json(['status' => 'already_paid', 'message' => 'Invoice sudah berstatus lunas.']);
        }

        $externalId = (string) ($payload['external_id'] ?? '');
        $paidAmount = (float) ($payload['paid_amount'] ?? ($payload['amount'] ?? 0));
        $detectedType = $payload['metadata']['payment_type'] ?? null;

        if (! $detectedType && preg_match('/^BC-INV-(\d+)-(dp|full)/i', $externalId, $m)) {
            $detectedType = strtolower($m[2]);
        }

        $settings = CompanySetting::get();
        $channel = $payload['payment_channel'] ?? ($payload['payment_method'] ?? 'XENDIT');

        $receiptUrl = $invoice->payment_token ? route('invoices.pay', $invoice->payment_token) : route('invoices.show', $invoice->id);
        $clientPhone = $invoice->client?->phone ?: ($invoice->project?->lead?->kontak_wa ?: ($invoice->subscription?->lead?->kontak_wa ?: null));
        $clientName = $invoice->client?->name ?: ($invoice->project?->lead?->nama_kontak ?: ($invoice->subscription?->lead?->nama_kontak ?: 'Klien'));

        // Tentukan apakah pembayaran ini adalah DP atau Pelunasan
        $isDp = ($detectedType === 'dp') || ($paidAmount < (float) $invoice->amount && $invoice->paid_amount == 0);

        // Idempotency untuk DP yang sudah tercatat
        if ($isDp && $invoice->status === 'partially_paid') {
            return response()->json(['status' => 'already_paid', 'message' => 'Pembayaran DP sudah tercatat sebelumnya.']);
        }

        try {
            if ($isDp) {
                // ==========================
                // PEMBAYARAN UANG MUKA (DP)
                // ==========================
                $newBalance = max(0, (float) $invoice->amount - $paidAmount);

                $invoice->update([
                    'status'                     => 'partially_paid',
                    'paid_amount'                => $paidAmount,
                    'balance_due'                => $newBalance,
                    'payment_method'             => $channel,
                    'payment_type'               => 'dp',
                    'payment_amount_transferred' => $paidAmount,
                    'payment_url'                => null,
                    'payment_reference'          => null,
                    'verified_at'                => now(),
                ]);

                // Sync ke data keuangan CRM
                $this->lifecycleService->recordInvoicePayment($invoice->fresh('project'), 'dp', $paidAmount, 'Xendit Gateway');

                // Notifikasi WhatsApp ke klien
                if (!empty($clientPhone)) {
                    $dpFormatted = number_format($paidAmount, 0, ',', '.');
                    $balanceFormatted = number_format($newBalance, 0, ',', '.');
                    $caption = "Halo Kak *{$clientName}*, terima kasih banyak! 🙏\n\n"
                             . "Pembayaran *Uang Muka (DP)* sebesar *Rp {$dpFormatted}* untuk tagihan *{$invoice->invoice_number}* telah kami terima dan dinyatakan *LUNAS OTOMATIS* via {$channel}.\n\n"
                             . "📌 *Total Nilai:* Rp " . number_format($invoice->amount, 0, ',', '.') . "\n"
                             . "💰 *DP Diterima:* Rp {$dpFormatted}\n"
                             . "⏳ *Sisa Tagihan Pelunasan:* Rp {$balanceFormatted}\n\n"
                             . "⚡ *Lihat / Unduh Kwitansi Resmi (Tanpa Login):*\n"
                             . "👉 {$receiptUrl}\n\n"
                             . "Tim pengembang kami kini mulai aktif mengerjakan proyek website Anda. Sisa tagihan dapat dilunasi melalui link di atas setelah tahap peninjauan selesai.\n\n"
                             . "Salam sukses dari *{$settings->company_name}*! 🚀";

                    try {
                        $this->waService->sendWhatsApp($clientPhone, $caption);
                    } catch (\Throwable $e) {
                        Log::warning("Gagal kirim WA konfirmasi DP otomatis: " . $e->getMessage());
                    }
                }

                Log::info("Invoice BuildClient {$invoice->invoice_number} berhasil diproses sbg DP via Xendit.");

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Pembayaran DP berhasil dicatat otomatis.',
                ]);

            } else {
                // ==========================
                // PEMBAYARAN LUNAS (FULL)
                // ==========================
                $pelunasanAmount = $invoice->balance_due > 0 ? (float) $invoice->balance_due : (float) $invoice->amount;
                $jenisPembayaran = $invoice->paid_amount > 0 ? 'pelunasan' : 'penuh';

                $invoice->update([
                    'status'                     => 'paid',
                    'paid_amount'                => (float) $invoice->amount,
                    'balance_due'                => 0,
                    'payment_method'             => $channel,
                    'payment_type'               => 'full',
                    'payment_amount_transferred' => $pelunasanAmount,
                    'payment_url'                => null,
                    'verified_at'                => now(),
                ]);

                // Sync ke data keuangan CRM
                $this->lifecycleService->recordInvoicePayment($invoice->fresh('project'), $jenisPembayaran, $pelunasanAmount, 'Xendit Gateway');

                // Jika ini adalah tagihan maintenance, perpanjang jatuh tempo otomatis
                if ($invoice->subscription_id && $invoice->subscription) {
                    $sub = $invoice->subscription;
                    $currentDue = $sub->tanggal_jatuh_tempo_berikutnya ? \Carbon\Carbon::parse($sub->tanggal_jatuh_tempo_berikutnya) : now();
                    $newDue = $currentDue->isPast() ? now()->addMonth() : $currentDue->copy()->addMonth();
                    $sub->update([
                        'tanggal_jatuh_tempo_berikutnya' => $newDue->toDateString(),
                        'status' => 'aktif',
                    ]);
                }

                // Notifikasi WhatsApp ke klien
                if (!empty($clientPhone)) {
                    $caption = "Halo Kak *{$clientName}*, terima kasih banyak! 🙏\n\n"
                             . "Pembayaran tagihan *{$invoice->invoice_number}* telah diterima dan berstatus *LUNAS PENUH* secara otomatis via {$channel}.\n\n"
                             . "⚡ *Lihat & Unduh Kwitansi Resmi Pelunasan (Tanpa Login):*\n"
                             . "👉 {$receiptUrl}\n\n"
                             . "Kwitansi pelunasan resmi bertanda tangan digital tersimpan aman pada tautan di atas dan dapat diunduh kapan saja.\n"
                             . "Salam sukses dari *{$settings->company_name}*! 🚀";

                    try {
                        $this->waService->sendWhatsApp($clientPhone, $caption);
                    } catch (\Throwable $e) {
                        Log::warning("Gagal kirim WA konfirmasi lunas otomatis: " . $e->getMessage());
                    }
                }

                Log::info("Invoice BuildClient {$invoice->invoice_number} berhasil dilunasi via Xendit.");

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Pembayaran LUNAS berhasil dicatat otomatis.',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('BuildClient Xendit webhook settlement exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
