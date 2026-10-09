<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Services\Payment\XenditService;
use App\Services\WhatsApp\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PublicInvoicePaymentController extends Controller
{
    /**
     * Tampilkan halaman pembayaran resmi yang aman (Tanpa Perlu Login).
     */
    public function show(string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['project.lead', 'client', 'subscription.lead', 'verifier'])
            ->firstOrFail();

        if ($invoice->status !== 'paid' && !empty($invoice->payment_reference)) {
            XenditService::checkAndSyncStatus($invoice);
        }

        $settings = CompanySetting::get();

        $dpVal = round((float) $invoice->amount / 2);
        $fullVal = (float) ($invoice->balance_due > 0 ? $invoice->balance_due : $invoice->amount);

        // Nomor WA bantuan Finance
        $waPhone = !empty($settings->phone_support)
            ? preg_replace('/[^0-9]/', '', $settings->phone_support)
            : '6285808749131';

        $waHelpText = rawurlencode(
            "Halo Tim Finance {$settings->company_name},\n"
            . "Saya ingin menanyakan perihal tagihan invoice resmi:\n"
            . "• No. Invoice: {$invoice->invoice_number}\n"
            . "• Proyek/Layanan: " . ($invoice->project?->name ?: ($invoice->subscription?->lead?->nama_usaha ?: $invoice->title)) . "\n"
            . "• Status: " . ($invoice->status_badge['label'] ?? 'Menunggu Pembayaran') . "\n\n"
            . "Mohon bantuannya, terima kasih!"
        );

        return view('invoices.public-pay', compact(
            'invoice',
            'settings',
            'dpVal',
            'fullVal',
            'waPhone',
            'waHelpText'
        ));
    }

    /**
     * Proses pembayaran instan otomatis via Gateway Xendit (Tanpa Login).
     */
    public function process(Request $request, string $token)
    {
        $invoice = Invoice::where('payment_token', $token)->firstOrFail();

        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.pay', $token)->with('info', 'Tagihan ini sudah lunas.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:dp,full',
            'channel'      => 'nullable|string',
        ]);

        $paymentType = $validated['payment_type'];
        if ($invoice->status === 'partially_paid') {
            $paymentType = 'full';
        }

        $channel = $validated['channel'] ?? 'online_payment';

        $result = XenditService::createInvoice($invoice, $paymentType, $channel);

        if (! $result['success']) {
            return redirect()->route('invoices.pay', $token)->with('error', $result['error'] ?? 'Gagal membuat sesi pembayaran.');
        }

        return redirect()->away($result['invoice_url']);
    }

    /**
     * Unduh lembar dokumen resmi Invoice PDF (Tanpa Login).
     */
    public function downloadPdf(string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['project.lead', 'client', 'subscription.lead'])
            ->firstOrFail();

        $settings = CompanySetting::get();

        $clientName = $invoice->client?->name
            ?: ($invoice->project?->lead?->nama_kontak ?: ($invoice->subscription?->lead?->nama_kontak ?: 'Klien'));
        $projectName = $invoice->project?->name
            ?: ($invoice->subscription?->lead?->nama_usaha ?: $invoice->title);
        $clientPhone = $invoice->client?->phone
            ?: ($invoice->project?->lead?->kontak_wa ?: ($invoice->subscription?->lead?->kontak_wa ?: ''));
        $clientEmail = $invoice->client?->email
            ?: ($invoice->project?->lead?->email ?: ($invoice->subscription?->lead?->email ?: ''));

        $projectAdapter = (object) [
            'id'             => $invoice->project_id ?? $invoice->id,
            'nama_project'   => $projectName,
            'harga'          => $invoice->amount,
            'total_terbayar' => $invoice->paid_amount,
            'sisa_tagihan'   => $invoice->balance_due,
            'paket'          => 'custom',
            'paket_label'    => 'Paket Layanan Software & Digital Solutions',
            'catatan'        => $invoice->title,
        ];

        $leadAdapter = (object) [
            'nama_kontak' => $clientName,
            'nama_usaha'  => $projectName,
            'kontak_wa'   => $clientPhone,
            'email'       => $clientEmail,
        ];

        $cleanInvoiceNo = str_replace('/', '-', $invoice->invoice_number);

        $data = [
            'project'       => $projectAdapter,
            'lead'          => $leadAdapter,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate'   => $invoice->created_at->translatedFormat('d F Y'),
            'dueDate'       => $invoice->due_date ? $invoice->due_date->translatedFormat('d F Y') : now()->addDays(7)->translatedFormat('d F Y'),
            'bankInfo'      => $settings->bank_info_string,
            'qrisBase64'    => $settings->qris_base64,
            'logoBase64'    => $settings->logo_base64,
            'signatureBase64'=> $settings->signature_base64,
            'settings'      => $settings,
            'isPdf'         => true,
        ];

        $pdf = Pdf::loadView('invoices.project', $data)->setPaper('a4', 'portrait');

        return $pdf->download("Invoice-{$cleanInvoiceNo}.pdf");
    }

    /**
     * Unduh Kwitansi Pelunasan / Bukti Sah PDF (Tanpa Login).
     */
    public function downloadReceipt(string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['project.lead', 'client', 'subscription.lead'])
            ->firstOrFail();

        if (!in_array($invoice->status, ['paid', 'partially_paid']) && $invoice->paid_amount <= 0) {
            return redirect()->route('invoices.pay', $token)->with('error', 'Kwitansi resmi belum tersedia karena tagihan belum dilakukan pembayaran.');
        }

        $settings = CompanySetting::get();

        $isDp = ($invoice->status === 'partially_paid' || ($invoice->paid_amount > 0 && $invoice->balance_due > 0));
        $receiptAmount = $invoice->paid_amount > 0 ? (float) $invoice->paid_amount : (float) $invoice->amount;
        $jenisLabel = $isDp ? 'Uang Muka (DP)' : 'Pelunasan Penuh';

        $receiptNumber = 'KW/' . ($invoice->verified_at ? $invoice->verified_at->format('Ym') : now()->format('Ym')) . '/' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($receiptAmount) . ' Rupiah';

        $clientName = $invoice->client?->name
            ?: ($invoice->project?->lead?->nama_kontak ?: ($invoice->subscription?->lead?->nama_kontak ?: 'Klien'));
        $projectName = $invoice->project?->name
            ?: ($invoice->subscription?->lead?->nama_usaha ?: $invoice->title);
        $clientPhone = $invoice->client?->phone
            ?: ($invoice->project?->lead?->kontak_wa ?: ($invoice->subscription?->lead?->kontak_wa ?: ''));
        $clientEmail = $invoice->client?->email
            ?: ($invoice->project?->lead?->email ?: ($invoice->subscription?->lead?->email ?: ''));

        $paymentAdapter = (object) [
            'id'           => $invoice->id,
            'jumlah'       => $receiptAmount,
            'metode_bayar' => $invoice->payment_method ?: 'Online Payment Gateway (QRIS / VA)',
            'jenis_label'  => $jenisLabel,
            'catatan'      => $invoice->payment_notes ?: "Pembayaran {$jenisLabel} Invoice {$invoice->invoice_number} - {$projectName}",
            'tanggal'      => $invoice->verified_at ?: now(),
        ];

        $projectAdapter = (object) [
            'id'             => $invoice->project_id ?? $invoice->id,
            'nama_project'   => $projectName,
            'harga'          => $invoice->amount,
            'total_terbayar' => $invoice->paid_amount,
            'sisa_tagihan'   => $invoice->balance_due,
        ];

        $leadAdapter = (object) [
            'nama_kontak' => $clientName,
            'nama_usaha'  => $projectName,
            'kontak_wa'   => $clientPhone,
            'email'       => $clientEmail,
        ];

        $cleanReceiptNo = str_replace('/', '-', $receiptNumber);

        $data = [
            'invoice'         => $invoice,
            'payment'         => $paymentAdapter,
            'project'         => $projectAdapter,
            'lead'            => $leadAdapter,
            'receiptNumber'   => $receiptNumber,
            'receiptDate'     => $invoice->verified_at ? $invoice->verified_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            'terbilang'       => $terbilang,
            'logoBase64'      => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings'        => $settings,
            'isPdf'           => true,
        ];

        $pdf = Pdf::loadView('invoices.receipt', $data)->setPaper('a4', 'portrait');

        return $pdf->download("Kwitansi-{$cleanReceiptNo}.pdf");
    }

    /**
     * Fallback: Upload bukti transfer manual jika klien membayar transfer offline.
     */
    public function uploadProof(Request $request, string $token, WhatsAppService $waService)
    {
        $invoice = Invoice::where('payment_token', $token)->firstOrFail();

        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'payment_type'  => 'required|in:dp,full',
            'payment_notes' => 'nullable|string|max:255',
        ], [
            'payment_proof.required' => 'Silakan pilih berkas bukti transfer.',
            'payment_proof.mimes'    => 'Format bukti harus berupa JPG, PNG, atau PDF.',
            'payment_proof.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        if ($invoice->payment_proof && Storage::disk('public')->exists($invoice->payment_proof)) {
            Storage::disk('public')->delete($invoice->payment_proof);
        }

        $paymentType = $invoice->status === 'partially_paid' ? 'full' : $request->input('payment_type', 'full');
        $claimedAmount = $paymentType === 'dp' ? round((float) $invoice->amount / 2) : (float) ($invoice->balance_due > 0 ? $invoice->balance_due : $invoice->amount);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $invoice->update([
            'payment_proof'              => $path,
            'payment_type'               => $paymentType,
            'payment_amount_transferred' => $claimedAmount,
            'payment_notes'              => $request->input('payment_notes'),
            'payment_proof_uploaded_at'  => now(),
            'status'                     => 'verifying',
        ]);

        $invoice->load(['client', 'project.lead', 'subscription.lead']);
        $settings = CompanySetting::get();

        $clientName = $invoice->client?->name ?: ($invoice->project?->lead?->nama_kontak ?: 'Klien');
        $projectName = $invoice->project?->name ?: ($invoice->subscription?->lead?->nama_usaha ?: $invoice->title);
        $typeLabel = $paymentType === 'dp' ? 'Uang Muka (DP)' : 'Pelunasan Penuh';
        $amountFormatted = number_format($claimedAmount, 0, ',', '.');
        $notes = $invoice->payment_notes ? "\n📝 *Catatan:* {$invoice->payment_notes}" : '';

        // Alert WhatsApp ke Admin
        $adminPhones = $settings->admin_alert_phones_array ?: ['085808749131'];
        $waMessage = "🔔 *[BUKTI TRANSFER MANUAL MASUK]*\n\n"
                   . "Klien *{$clientName}* telah mengunggah bukti pembayaran *{$typeLabel}* untuk tagihan *{$invoice->invoice_number}* ({$projectName}).\n\n"
                   . "💰 *Nominal Klaim:* Rp {$amountFormatted}{$notes}\n\n"
                   . "Mohon verifikasi mutasi rekening di CRM Admin:\n"
                   . route('invoices.show', $invoice->id);

        foreach ($adminPhones as $adminPhone) {
            try {
                $waService->sendWhatsApp($adminPhone, $waMessage);
            } catch (\Throwable $e) {
                Log::warning("Gagal kirim WA alert ke admin {$adminPhone}: " . $e->getMessage());
            }
        }

        return redirect()->route('invoices.pay', $token)->with('success', 'Bukti transfer berhasil dikirim. Tim Finance kami akan segera memverifikasi mutasi rekening.');
    }

    private function terbilang($angka): string
    {
        $angka = abs((float) $angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($angka < 12) {
            return $baca[(int) $angka];
        }
        if ($angka < 20) {
            return $this->terbilang($angka - 10) . ' Belas';
        }
        if ($angka < 100) {
            return $this->terbilang($angka / 10) . ' Puluh ' . $this->terbilang($angka % 10);
        }
        if ($angka < 200) {
            return 'Seratus ' . $this->terbilang($angka - 100);
        }
        if ($angka < 1000) {
            return $this->terbilang($angka / 100) . ' Ratus ' . $this->terbilang($angka % 100);
        }
        if ($angka < 2000) {
            return 'Seribu ' . $this->terbilang($angka - 1000);
        }
        if ($angka < 1000000) {
            return $this->terbilang($angka / 1000) . ' Ribu ' . $this->terbilang($angka % 1000);
        }
        if ($angka < 1000000000) {
            return $this->terbilang($angka / 1000000) . ' Juta ' . $this->terbilang($angka % 1000000);
        }
        if ($angka < 1000000000000) {
            return $this->terbilang($angka / 1000000000) . ' Milyar ' . $this->terbilang($angka % 1000000000);
        }

        return 'Jumlah Terlalu Besar';
    }
}
