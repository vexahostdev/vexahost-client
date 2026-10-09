<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->can('invoices.manage');

        $query = Invoice::query()->with(['project', 'client']);

        if (!$isAdmin) {
            $query->where('client_id', $user->id);
        }

        // Search or filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('project', fn($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        // Stats calculation
        $statsQuery = Invoice::query();
        if (!$isAdmin) {
            $statsQuery->where('client_id', $user->id);
        }

        $stats = [
            'total_amount' => (float) (clone $statsQuery)->sum('amount'),
            'total_paid' => (float) (clone $statsQuery)->sum('paid_amount'),
            'total_due' => (float) (clone $statsQuery)->where('status', '!=', 'paid')->sum('balance_due'),
            'verifying_count' => (int) (clone $statsQuery)->where('status', 'verifying')->count(),
            'unpaid_count' => (int) (clone $statsQuery)->where('status', 'unpaid')->count(),
        ];

        $invoices = $query->latest()->paginate(10)->withQueryString();

        return view('invoices.index', compact('invoices', 'stats', 'isAdmin'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->can('invoices.manage');

        if (!$isAdmin && $invoice->client_id !== $user->id) {
            abort(403, 'Anda tidak memiliki otorisasi untuk melihat invoice ini.');
        }

        $invoice->load(['project', 'client', 'verifier']);
        if ($invoice->status !== 'paid' && !empty($invoice->payment_reference)) {
            \App\Services\Payment\XenditService::checkAndSyncStatus($invoice);
        }
        $settings = \App\Models\CompanySetting::get();
        $bankInfo = $settings->bank_info_string;

        return view('invoices.show', compact('invoice', 'bankInfo', 'isAdmin', 'settings'));
    }

    /**
     * Inisialisasi pembayaran otomatis via gateway Xendit untuk Client Invoice.
     * Hanya mendukung 3 metode: QRIS, MANDIRI VA, BNI VA.
     */
    public function payWithXendit(Request $request, Invoice $invoice)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->can('invoices.manage');

        if (!$isAdmin && $invoice->client_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk membayar invoice ini.');
        }

        if ($invoice->status === 'paid') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tagihan ini sudah lunas.'], 400);
            }
            return back()->with('info', 'Tagihan ini sudah lunas.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:dp,full',
            'channel'      => 'nullable|string|in:online_payment,all,qris,mandiri_va,bni_va,mandiri,bni',
        ]);

        $paymentType = $validated['payment_type'];
        if ($invoice->status === 'partially_paid') {
            $paymentType = 'full';
        }

        $channel = $validated['channel'] ?? 'online_payment';

        $result = \App\Services\Payment\XenditService::createInvoice($invoice, $paymentType, $channel);

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result['error']], 422);
            }
            return back()->with('error', $result['error']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'invoice_url' => $result['invoice_url'],
            ]);
        }

        return redirect()->away($result['invoice_url']);
    }

    /**
     * Client upload payment proof (transfer receipt / QRIS screenshot).
     */
    public function uploadPaymentProof(Request $request, Invoice $invoice, WhatsAppService $waService)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->can('invoices.manage');

        if (!$isAdmin && $invoice->client_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengunggah bukti pembayaran invoice ini.');
        }

        $request->validate([
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'payment_type' => 'nullable|in:dp,full',
            'payment_amount_transferred' => 'nullable|numeric|min:1',
            'payment_notes' => 'nullable|string|max:500',
        ], [
            'payment_proof.required' => 'Silakan pilih file bukti transfer/struk QRIS terlebih dahulu.',
            'payment_proof.mimes' => 'Format file bukti harus berupa JPG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran file maksimal 5MB.',
        ]);

        // Delete previous proof if exists
        if ($invoice->payment_proof && Storage::disk('public')->exists($invoice->payment_proof)) {
            Storage::disk('public')->delete($invoice->payment_proof);
        }

        $paymentType = $invoice->status === 'partially_paid' ? 'full' : ($request->input('payment_type', 'full'));
        $claimedAmount = $request->filled('payment_amount_transferred')
            ? (float) $request->input('payment_amount_transferred')
            : ($paymentType === 'dp' ? round($invoice->amount / 2) : (float) ($invoice->balance_due > 0 ? $invoice->balance_due : $invoice->amount));

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $invoice->update([
            'payment_proof' => $path,
            'payment_type' => $paymentType,
            'payment_amount_transferred' => $claimedAmount,
            'payment_notes' => $request->input('payment_notes'),
            'payment_proof_uploaded_at' => now(),
            'status' => 'verifying',
        ]);

        $invoice->load(['client', 'project']);
        $settings = \App\Models\CompanySetting::get();

        // 1. Dispatch Instant WhatsApp Alert to Admin Numbers
        $adminPhones = $settings->admin_alert_phones_array ?: ['085808749131'];

        $clientName = $invoice->client?->name ?? 'Klien';
        $projectName = $invoice->project?->name ?? 'Proyek';
        $typeLabel = $paymentType === 'dp' ? 'Uang Muka (DP)' : 'Pelunasan Penuh';
        $amountFormatted = number_format($claimedAmount, 0, ',', '.');
        $notes = $invoice->payment_notes ? "\n📝 *Catatan:* {$invoice->payment_notes}" : '';

        $waMessage = "🔔 *NOTIFIKASI PEMBAYARAN MASUK (PORTAL KLIEN)*\n\n"
                   . "Halo Admin VexaHost,\n"
                   . "Klien *{$clientName}* telah mengunggah bukti pembayaran *{$typeLabel}* untuk tagihan *{$invoice->invoice_number}*.\n\n"
                   . "📌 *Proyek:* {$projectName}\n"
                   . "💵 *Jenis:* {$typeLabel}\n"
                   . "💰 *Nominal Klaim:* Rp {$amountFormatted}{$notes}\n\n"
                   . "Mohon segera verifikasi bukti pembayaran melalui tautan berikut:\n"
                   . route('invoices.show', $invoice->id);

        foreach ($adminPhones as $adminPhone) {
            try {
                $waService->sendWhatsApp($adminPhone, $waMessage);
            } catch (\Throwable $e) {
                Log::warning("Gagal kirim WA alert pembayaran ke admin {$adminPhone}: " . $e->getMessage());
            }
        }

        // 2. Dispatch Email Alert to Admin
        try {
            $emailDest = $settings->email_internal_alert ?: 'vexahostcloudtech@gmail.com';
            Mail::raw(
                "Halo Tim Finance VexaHost,\n\n"
                . "Klien {$clientName} telah mengunggah bukti transfer [{$typeLabel}] untuk tagihan {$invoice->invoice_number} ({$projectName}).\n"
                . "Nominal: Rp {$amountFormatted}\n"
                . "Catatan Klien: " . ($invoice->payment_notes ?: '-') . "\n\n"
                . "Silakan login ke Client Panel atau CRM untuk memverifikasi pembayaran ini:\n"
                . route('invoices.show', $invoice->id) . "\n\n"
                . "Salam,\nSistem Otomasi Client Panel",
                function ($m) use ($emailDest, $invoice, $typeLabel) {
                    $m->to($emailDest)->subject("[Pembayaran Masuk - {$typeLabel}] Bukti Transfer Invoice {$invoice->invoice_number}");
                }
            );
        } catch (\Throwable $e) {
            Log::warning("Gagal kirim email alert pembayaran ke admin: " . $e->getMessage());
        }

        $successMsg = $paymentType === 'dp'
            ? 'Bukti pembayaran Uang Muka (DP) berhasil diunggah! Tim Finance telah menerima notifikasi dan akan segera memverifikasi mutasi rekening Anda.'
            : 'Bukti pembayaran pelunasan berhasil diunggah! Notifikasi instan telah dikirimkan ke Tim Finance untuk proses verifikasi.';

        return back()->with('success', $successMsg);
    }

    /**
     * Admin verifies or rejects client's payment proof.
     */
    public function verifyPayment(Request $request, Invoice $invoice, WhatsAppService $waService)
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->can('invoices.manage')) {
            abort(403, 'Hanya admin yang berwenang memverifikasi pembayaran.');
        }

        $action = $request->input('action'); // 'approve_dp', 'approve_full', 'approve', 'reject'

        if ($action === 'approve_dp') {
            $dpAmount = (float) $request->input('dp_amount', $invoice->payment_amount_transferred ?: round($invoice->amount / 2));
            if ($dpAmount <= 0 || $dpAmount >= $invoice->amount) {
                $dpAmount = round($invoice->amount / 2);
            }

            $newBalance = max(0, $invoice->amount - $dpAmount);

            $invoice->update([
                'status' => 'partially_paid',
                'paid_amount' => $dpAmount,
                'balance_due' => $newBalance,
                'verified_at' => now(),
                'verified_by' => $user->id,
            ]);

            // Sync to CRM
            $this->syncPaymentToCrm($invoice, 'dp', $dpAmount, $user->name);

            // Notify client via WhatsApp
            $invoice->load(['client', 'project']);
            $settings = \App\Models\CompanySetting::get();
            if (!empty($invoice->client?->phone)) {
                $dpFormatted = number_format($dpAmount, 0, ',', '.');
                $balanceFormatted = number_format($newBalance, 0, ',', '.');
                $caption = "Halo Kak *{$invoice->client->name}*, terima kasih banyak! 🙏\n\n"
                         . "Pembayaran *Uang Muka (DP)* sebesar *Rp {$dpFormatted}* untuk tagihan *{$invoice->invoice_number}* ({$invoice->project->name}) telah kami verifikasi dan dinyatakan *SAH*.\n\n"
                         . "📌 *Total Nilai Proyek:* Rp " . number_format($invoice->amount, 0, ',', '.') . "\n"
                         . "💰 *DP Diterima:* Rp {$dpFormatted}\n"
                         . "⏳ *Sisa Tagihan Pelunasan:* Rp {$balanceFormatted}\n\n"
                         . "Tim pengembang kami kini mulai aktif mengerjakan proyek website Anda. Sisa tagihan dapat dilunasi setelah tahap peninjauan selesai.\n\n"
                         . "Salam sukses dari *{$settings->company_name}*! 🚀";

                try {
                    $waService->sendWhatsApp($invoice->client->phone, $caption);
                } catch (\Throwable $e) {
                    Log::warning("Gagal kirim WA konfirmasi DP ke klien: " . $e->getMessage());
                }
            }

            return back()->with('success', "Pembayaran Uang Muka (DP) sebesar Rp " . number_format($dpAmount, 0, ',', '.') . " berhasil diverifikasi! Status tagihan: DP Diterima (Sisa Tagihan: Rp " . number_format($newBalance, 0, ',', '.') . ").");

        } elseif ($action === 'approve_full' || $action === 'approve') {
            $paidTotal = $invoice->amount;
            $pelunasanAmount = $invoice->balance_due > 0 ? (float) $invoice->balance_due : (float) $invoice->amount;
            $jenisPembayaran = $invoice->paid_amount > 0 ? 'pelunasan' : 'penuh';

            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $paidTotal,
                'balance_due' => 0,
                'verified_at' => now(),
                'verified_by' => $user->id,
            ]);

            // Sync to CRM
            $this->syncPaymentToCrm($invoice, $jenisPembayaran, $pelunasanAmount, $user->name);

            // Notify client via WhatsApp
            $invoice->load(['client', 'project']);
            $settings = \App\Models\CompanySetting::get();
            if (!empty($invoice->client?->phone)) {
                $caption = "Halo Kak *{$invoice->client->name}*, terima kasih banyak! 🙏\n\n"
                         . "Pembayaran Anda untuk *{$invoice->invoice_number}* pada proyek *{$invoice->project->name}* telah kami verifikasi dan berstatus *LUNAS*.\n\n"
                         . "Anda dapat mengunduh bukti kwitansi resmi kapan saja melalui Client Panel.\n"
                         . "Salam sukses dari *{$settings->company_name}*! 🚀";

                try {
                    $waService->sendWhatsApp($invoice->client->phone, $caption);
                } catch (\Throwable $e) {
                    Log::warning("Gagal kirim WA konfirmasi lunas ke klien: " . $e->getMessage());
                }
            }

            return back()->with('success', "Invoice {$invoice->invoice_number} berhasil diverifikasi sebagai LUNAS!");

        } elseif ($action === 'reject') {
            $invoice->update([
                'status' => ($invoice->paid_amount > 0 ? 'partially_paid' : 'unpaid'),
            ]);

            return back()->with('error', "Bukti transfer ditolak. Status invoice dikembalikan ke belum lunas.");
        }

        return back()->with('error', 'Aksi tidak valid.');
    }

    /**
     * Catat pembayaran terverifikasi ke data keuangan Admin Panel.
     */
    protected function syncPaymentToCrm(Invoice $invoice, string $jenis, float $amount, ?string $verifierName = null): void
    {
        try {
            app(\App\Services\ProjectLifecycleService::class)->recordInvoicePayment($invoice->fresh('project'), $jenis, $amount, $verifierName);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat pembayaran ke Admin Panel: ' . $e->getMessage());
        }
    }

    /**
     * Download enterprise Invoice PDF directly in portal.
     */
    public function downloadPdf(Invoice $invoice)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->can('invoices.manage');

        if (!$isAdmin && $invoice->client_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengunduh invoice ini.');
        }

        $invoice->load(['project', 'client']);
        $settings = \App\Models\CompanySetting::get();

        // Standardize object for project.blade.php template
        $projectAdapter = (object) [
            'id' => $invoice->project_id,
            'nama_project' => $invoice->project?->name ?? 'Proyek Klien',
            'harga' => $invoice->amount,
            'total_terbayar' => $invoice->paid_amount,
            'sisa_tagihan' => $invoice->balance_due,
            'paket' => 'custom',
            'paket_label' => 'Paket Layanan Software & Digital Solutions',
            'catatan' => $invoice->title,
        ];

        $leadAdapter = (object) [
            'nama_kontak' => $invoice->client?->name ?? 'Klien',
            'nama_usaha' => $invoice->project?->name ?? 'Perusahaan Klien',
            'kontak_wa' => $invoice->client?->phone ?? '',
            'email' => $invoice->client?->email ?? '',
        ];

        $cleanInvoiceNo = str_replace('/', '-', $invoice->invoice_number);

        $data = [
            'project' => $projectAdapter,
            'lead' => $leadAdapter,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => $invoice->created_at->translatedFormat('d F Y'),
            'dueDate' => $invoice->due_date ? $invoice->due_date->translatedFormat('d F Y') : now()->addDays(7)->translatedFormat('d F Y'),
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.project', $data)->setPaper('a4', 'portrait');
        return $pdf->download("Invoice-{$cleanInvoiceNo}.pdf");
    }

    /**
     * Download official PDF Dokumen Tagihan Pelunasan (Settlement Invoice).
     */
    public function settlementPdf(Request $request, Invoice $invoice)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('ceo') || $user->can('invoices.manage');

        if (!$isAdmin && $invoice->client_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengunduh dokumen pelunasan ini.');
        }

        $invoice->load(['project', 'client']);
        $settings = \App\Models\CompanySetting::get();

        $projectAdapter = (object) [
            'id' => $invoice->project_id,
            'nama_project' => $invoice->project?->name ?? 'Proyek Klien',
            'harga' => $invoice->amount,
            'total_paid' => $invoice->paid_amount,
            'remaining_balance' => $invoice->balance_due,
            'paket_label' => 'Paket Layanan Software & Digital Solutions',
            'created_at' => $invoice->created_at,
        ];

        $leadAdapter = (object) [
            'nama_kontak' => $invoice->client?->name ?? 'Klien',
            'nama_usaha' => $invoice->project?->name ?? 'Perusahaan Klien',
            'kontak_wa' => $invoice->client?->phone ?? '',
            'email' => $invoice->client?->email ?? '',
        ];

        $settlementNumber = 'INV-SETTLE/' . ($invoice->created_at ? $invoice->created_at->format('Ym') : now()->format('Ym')) . '/' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($invoice->balance_due) . ' Rupiah';

        $data = [
            'project' => $projectAdapter,
            'lead' => $leadAdapter,
            'settlementNumber' => $settlementNumber,
            'settlementDate' => now()->translatedFormat('d F Y'),
            'dueDate' => $invoice->due_date ? $invoice->due_date->translatedFormat('d F Y') : now()->addDays(5)->translatedFormat('d F Y'),
            'terbilang' => $terbilang,
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        $cleanSettlementNo = str_replace('/', '-', $settlementNumber);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.settlement', $data)->setPaper('a4', 'portrait');
        return $pdf->download("Dokumen-Pelunasan-{$cleanSettlementNo}.pdf");
    }

    /**
     * Show or Download official Kwitansi (Payment Receipt) for this invoice.
     */
    public function receipt(Request $request, Invoice $invoice)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('ceo') || $user->can('invoices.manage');

        if (!$isAdmin && $invoice->client_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk melihat kwitansi ini.');
        }

        $invoice->load(['project', 'client']);
        $settings = \App\Models\CompanySetting::get();

        $isDp = ($invoice->status === 'partially_paid' || ($invoice->paid_amount > 0 && $invoice->balance_due > 0));
        $receiptAmount = $invoice->paid_amount > 0 ? (float)$invoice->paid_amount : (float)$invoice->amount;
        $jenisLabel = $isDp ? 'Uang Muka (DP)' : 'Pelunasan Penuh';

        $receiptNumber = 'KW/' . ($invoice->verified_at ? $invoice->verified_at->format('Ym') : now()->format('Ym')) . '/' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($receiptAmount) . ' Rupiah';

        $paymentAdapter = (object) [
            'id' => $invoice->id,
            'jumlah' => $receiptAmount,
            'metode_bayar' => 'Transfer Bank / QRIS Resmi',
            'jenis_label' => $jenisLabel,
            'catatan' => $invoice->payment_notes ?: "Pembayaran {$jenisLabel} Invoice {$invoice->invoice_number} - {$invoice->project?->name}",
            'tanggal' => $invoice->verified_at ?: now(),
        ];

        $projectAdapter = (object) [
            'id' => $invoice->project_id,
            'nama_project' => $invoice->project?->name ?? 'Proyek Klien',
            'harga' => $invoice->amount,
            'total_terbayar' => $invoice->paid_amount,
            'sisa_tagihan' => $invoice->balance_due,
        ];

        $leadAdapter = (object) [
            'nama_kontak' => $invoice->client?->name ?? 'Klien',
            'nama_usaha' => $invoice->project?->name ?? 'Perusahaan Klien',
            'kontak_wa' => $invoice->client?->phone ?? '',
            'email' => $invoice->client?->email ?? '',
        ];

        $cleanReceiptNo = str_replace('/', '-', $receiptNumber);

        $data = [
            'invoice' => $invoice,
            'payment' => $paymentAdapter,
            'project' => $projectAdapter,
            'lead' => $leadAdapter,
            'receiptNumber' => $receiptNumber,
            'receiptDate' => $invoice->verified_at ? $invoice->verified_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            'terbilang' => $terbilang,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => false,
        ];

        $format = strtolower($request->get('format', 'html'));
        if ($format === 'pdf') {
            $data['isPdf'] = true;
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.receipt', $data)->setPaper('a4', 'portrait');
            return $pdf->download("Kwitansi-{$cleanReceiptNo}.pdf");
        }

        if ($format === 'word' || $format === 'doc') {
            $data['isPdf'] = true;
            $html = view('invoices.receipt', $data)->render();
            return response($html, 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"Kwitansi-{$cleanReceiptNo}.doc\"",
            ]);
        }

        return view('invoices.receipt', $data);
    }

    /**
     * Backward-compatible alias for downloading receipt PDF.
     */
    public function downloadReceipt(Request $request, Invoice $invoice)
    {
        $request->merge(['format' => 'pdf']);
        return $this->receipt($request, $invoice);
    }

    /**
     * Helper to convert number to Indonesian words (Terbilang).
     */
    private function terbilang($angka): string
    {
        $angka = abs((int)$angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($angka < 12) {
            return $baca[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            return $this->terbilang(intval($angka / 10)) . ' Puluh ' . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return 'Seratus ' . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang(intval($angka / 100)) . ' Ratus ' . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return 'Seribu ' . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang(intval($angka / 1000)) . ' Ribu ' . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return $this->terbilang(intval($angka / 1000000)) . ' Juta ' . $this->terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            return $this->terbilang(intval($angka / 1000000000)) . ' Miliar ' . $this->terbilang($angka % 1000000000);
        }

        return (string)$angka;
    }
}
