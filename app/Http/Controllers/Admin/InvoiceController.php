<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Project;
use App\Models\Payment;
use App\Models\MaintenanceSubscription;
use App\Models\CompanySetting;
use App\Mail\ProjectInvoiceMail;
use App\Mail\SettlementInvoiceMail;
use App\Mail\MaintenanceInvoiceMail;
use App\Mail\PaymentReceiptMail;
use App\Services\ActivityLogger;
use App\Services\ProjectLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Show Project Invoice (HTML, PDF, Word).
     */
    public function projectInvoice(Request $request, Project $project)
    {
        app(ProjectLifecycleService::class)->syncInvoiceForProject($project);
        $project->load(['lead', 'payments', 'latestInvoice']);

        $invoiceNumber = 'INV/' . $project->created_at->format('Ym') . '/' . str_pad($project->id, 4, '0', STR_PAD_LEFT);
        
        ActivityLogger::log('view_invoice', "Melihat/mencetak invoice {$invoiceNumber} untuk project {$project->nama_project}", 'Project', $project->id);

        $settings = CompanySetting::get();

        $data = [
            'project' => $project,
            'lead' => $project->lead,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => $project->created_at->translatedFormat('d F Y'),
            'dueDate' => $project->created_at->addDays(7)->translatedFormat('d F Y'),
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => false,
        ];

        $cleanInvoiceNo = str_replace('/', '-', $invoiceNumber);
        $format = strtolower($request->get('format', 'html'));
        if ($format === 'pdf') {
            $data['isPdf'] = true;
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.project', $data)->setPaper('a4', 'portrait');
            return $pdf->download("Invoice-{$cleanInvoiceNo}.pdf");
        }
        if ($format === 'word' || $format === 'doc') {
            $data['isPdf'] = true;
            $html = view('admin.invoices.project', $data)->render();
            return response($html, 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"Invoice-{$cleanInvoiceNo}.doc\"",
            ]);
        }

        return view('admin.invoices.project', $data);
    }

    /**
     * Show Official Kwitansi (Payment Receipt) (HTML, PDF, Word).
     */
    public function paymentReceipt(Request $request, Payment $payment)
    {
        $payment->load('project.lead');
        $project = $payment->project;
        $lead = $project?->lead;

        $receiptNumber = 'KW/' . ($payment->tanggal ? $payment->tanggal->format('Ym') : now()->format('Ym')) . '/' . str_pad($payment->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($payment->jumlah) . ' Rupiah';

        ActivityLogger::log('view_receipt', "Melihat/mencetak kwitansi {$receiptNumber} senilai Rp " . number_format($payment->jumlah, 0, ',', '.'), 'Payment', $payment->id);
 
        $settings = CompanySetting::get();

        $data = [
            'payment' => $payment,
            'project' => $project,
            'lead' => $lead,
            'receiptNumber' => $receiptNumber,
            'receiptDate' => $payment->tanggal ? $payment->tanggal->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            'terbilang' => $terbilang,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => false,
        ];

        $cleanReceiptNo = str_replace('/', '-', $receiptNumber);
        $format = strtolower($request->get('format', 'html'));
        if ($format === 'pdf') {
            $data['isPdf'] = true;
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.receipt', $data)->setPaper('a4', 'portrait');
            return $pdf->download("Kwitansi-{$cleanReceiptNo}.pdf");
        }
        if ($format === 'word' || $format === 'doc') {
            $data['isPdf'] = true;
            $html = view('admin.invoices.receipt', $data)->render();
            return response($html, 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"Kwitansi-{$cleanReceiptNo}.doc\"",
            ]);
        }

        return view('admin.invoices.receipt', $data);
    }

    /**
     * Show Maintenance Subscription Invoice (HTML, PDF, Word).
     */
    public function maintenanceInvoice(Request $request, MaintenanceSubscription $subscription)
    {
        $subscription->load('project.lead');
        $project = $subscription->project;
        $lead = $project?->lead;

        $invoiceNumber = 'MNT/' . now()->format('Ym') . '/' . str_pad($subscription->id, 4, '0', STR_PAD_LEFT);

        ActivityLogger::log('view_maintenance_invoice', "Melihat/mencetak invoice maintenance {$invoiceNumber}", 'MaintenanceSubscription', $subscription->id);

        $settings = CompanySetting::get();

        $data = [
            'subscription' => $subscription,
            'project' => $project,
            'lead' => $lead,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => now()->translatedFormat('d F Y'),
            'dueDate' => $subscription->tanggal_jatuh_tempo_berikutnya ? $subscription->tanggal_jatuh_tempo_berikutnya->translatedFormat('d F Y') : now()->addDays(7)->translatedFormat('d F Y'),
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => false,
        ];

        $cleanMntNo = str_replace('/', '-', $invoiceNumber);
        $format = strtolower($request->get('format', 'html'));
        if ($format === 'pdf') {
            $data['isPdf'] = true;
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.maintenance', $data)->setPaper('a4', 'portrait');
            return $pdf->download("Invoice-Maintenance-{$cleanMntNo}.pdf");
        }
        if ($format === 'word' || $format === 'doc') {
            $data['isPdf'] = true;
            $html = view('admin.invoices.maintenance', $data)->render();
            return response($html, 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"Invoice-Maintenance-{$cleanMntNo}.doc\"",
            ]);
        }

        return view('admin.invoices.maintenance', $data);
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
            return trim($this->terbilang($angka - 10) . ' Belas');
        } elseif ($angka < 100) {
            return trim($this->terbilang((int)($angka / 10)) . ' Puluh ' . $this->terbilang($angka % 10));
        } elseif ($angka < 200) {
            return trim('Seratus ' . $this->terbilang($angka - 100));
        } elseif ($angka < 1000) {
            return trim($this->terbilang((int)($angka / 100)) . ' Ratus ' . $this->terbilang($angka % 100));
        } elseif ($angka < 2000) {
            return trim('Seribu ' . $this->terbilang($angka - 1000));
        } elseif ($angka < 1000000) {
            return trim($this->terbilang((int)($angka / 1000)) . ' Ribu ' . $this->terbilang($angka % 1000));
        } elseif ($angka < 1000000000) {
            return trim($this->terbilang((int)($angka / 1000000)) . ' Juta ' . $this->terbilang($angka % 1000000));
        } elseif ($angka < 1000000000000) {
            return trim($this->terbilang((int)($angka / 1000000000)) . ' Miliar ' . $this->terbilang($angka % 1000000000));
        }

        return (string)$angka;
    }

    /**
     * Helper to get Base64 encoded Logo image for HTML, PDF, and Word compatibility.
     */
    private function getLogoBase64(): ?string
    {
        return CompanySetting::get()->logo_base64;
    }

    /**
     * Helper to get Base64 encoded QRIS image for HTML, PDF, and Word compatibility.
     */
    private function getQrisBase64(): ?string
    {
        return CompanySetting::get()->qris_base64;
    }

    /**
     * Generate and dispatch official PDF Invoice to client via VexaHost WA Gateway.
     */
    public function sendProjectInvoiceWa(Request $request, Project $project, \App\Services\WhatsApp\WhatsAppService $waService)
    {
        $project->load(['lead', 'payments', 'invoices']);
        $lead = $project->lead;
        if (!$lead || empty($lead->kontak_wa)) {
            return back()->with('error', 'Nomor WhatsApp klien tidak ditemukan.');
        }

        $invoiceNumber = 'INV/' . $project->created_at->format('Ym') . '/' . str_pad($project->id, 4, '0', STR_PAD_LEFT);
        $settings = CompanySetting::get();

        $data = [
            'project' => $project,
            'lead' => $lead,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => $project->created_at->translatedFormat('d F Y'),
            'dueDate' => $project->created_at->addDays(7)->translatedFormat('d F Y'),
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.project', $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();
        $cleanInvoiceNo = str_replace('/', '-', $invoiceNumber);
        $filename = "Invoice-{$project->id}-{$cleanInvoiceNo}.pdf";

        $brandName = $settings->brand_name ?: 'VexaHost';
        
        $invoice = $project->latestInvoice ?: $project->invoices()->first();
        if (!$invoice) {
            $invoice = app(ProjectLifecycleService::class)->syncInvoiceForProject($project);
        }
        if (empty($invoice->payment_token)) {
            $invoice->payment_token = Str::random(40);
            $invoice->saveQuietly();
        }
        $directPayUrl = route('invoices.pay', $invoice->payment_token);

        $clientName = $lead->nama_kontak ?: $lead->nama_usaha;
        $caption = "Halo Kak *{$clientName}*, terlampir dokumen Invoice resmi untuk proyek *{$project->nama_project}*.\n\n"
                 . "📄 *No. Invoice:* {$invoiceNumber}\n"
                 . "💰 *Total Nilai:* Rp " . number_format($project->harga, 0, ',', '.') . "\n"
                 . "💳 *Sisa Tagihan:* Rp " . number_format($project->remaining_balance, 0, ',', '.') . "\n\n"
                 . "⚡ *Link Pembayaran Langsung (Tanpa Perlu Login):*\n"
                 . "👉 {$directPayUrl}\n\n"
                 . "✨ *Metode Otomatis (Instan 24 Jam):*\n"
                 . "• QRIS Semua Bank & E-Wallet (BCA, Livin, GoPay, OVO, Dana)\n"
                 . "• Virtual Account Bank Mandiri & BNI\n\n"
                 . "Kwitansi resmi lunas bertanda tangan digital terbit otomatis setelah pembayaran berhasil tanpa perlu konfirmasi manual. Terima kasih! 🚀\n- {$brandName}";

        $res = $waService->sendMediaWhatsApp(
            to: $lead->kontak_wa,
            fileContent: $pdfContent,
            filename: $filename,
            caption: $caption,
            lead: $lead,
            tipePesan: 'invoice_pdf'
        );

        if ($res['success'] ?? false) {
            ActivityLogger::log('send_invoice_wa', "Mengirim invoice PDF {$invoiceNumber} via WA ke {$lead->kontak_wa}", 'Project', $project->id);
            return back()->with('success', "📄 Dokumen PDF Invoice {$invoiceNumber} berhasil dikirim ke WhatsApp {$lead->kontak_wa}!");
        }

        return back()->with('error', "Gagal mengirim PDF Invoice via WhatsApp: " . ($res['message'] ?? 'Terjadi kesalahan'));
    }

    /**
     * Dispatch official PDF Invoice to client via Email.
     */
    public function sendProjectInvoiceEmail(Request $request, Project $project)
    {
        $project->load(['lead', 'payments', 'invoices']);
        $lead = $project->lead;
        if (!$lead || empty($lead->email)) {
            return back()->with('error', 'Alamat email klien tidak ditemukan pada data Lead/Proyek.');
        }

        $invoiceNumber = 'INV/' . $project->created_at->format('Ym') . '/' . str_pad($project->id, 4, '0', STR_PAD_LEFT);
        $settings = CompanySetting::get();

        $data = [
            'project' => $project,
            'lead' => $lead,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => $project->created_at->translatedFormat('d F Y'),
            'dueDate' => $project->created_at->addDays(7)->translatedFormat('d F Y'),
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.project', $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();
        $cleanInvoiceNo = str_replace('/', '-', $invoiceNumber);
        $filename = "Invoice-{$project->id}-{$cleanInvoiceNo}.pdf";

        $invoice = $project->latestInvoice ?: $project->invoices()->first();
        if (!$invoice) {
            $invoice = app(ProjectLifecycleService::class)->syncInvoiceForProject($project);
        }
        if (empty($invoice->payment_token)) {
            $invoice->payment_token = Str::random(40);
            $invoice->saveQuietly();
        }
        $directPayUrl = route('invoices.pay', $invoice->payment_token);

        try {
            Mail::to($lead->email)->send(
                new ProjectInvoiceMail($project, $invoice, $settings, $pdfContent, $filename, $directPayUrl)
            );

            ActivityLogger::log('send_invoice_email', "Mengirim invoice PDF {$invoiceNumber} via Email ke {$lead->email}", 'Project', $project->id);
            return back()->with('success', "✉️ Dokumen PDF Invoice {$invoiceNumber} berhasil dikirim ke email {$lead->email}!");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal mengirim Email Invoice: " . $e->getMessage());
        }
    }

    /**
     * Generate and dispatch official PDF Kwitansi (Payment Receipt) to client via WhatsApp Gateway.
     */
    public function sendPaymentReceiptWa(Request $request, Payment $payment, \App\Services\WhatsApp\WhatsAppService $waService)
    {
        $payment->load('project.lead');
        $project = $payment->project;
        $lead = $project?->lead;

        if (!$lead || empty($lead->kontak_wa)) {
            return back()->with('error', 'Nomor WhatsApp klien tidak ditemukan.');
        }

        $receiptNumber = 'KW/' . ($payment->tanggal ? $payment->tanggal->format('Ym') : now()->format('Ym')) . '/' . str_pad($payment->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($payment->jumlah) . ' Rupiah';
        $settings = CompanySetting::get();

        $data = [
            'payment' => $payment,
            'project' => $project,
            'lead' => $lead,
            'receiptNumber' => $receiptNumber,
            'receiptDate' => $payment->tanggal ? $payment->tanggal->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            'terbilang' => $terbilang,
            'logoBase64' => $settings->logo_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.receipt', $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();
        $cleanReceiptNo = str_replace('/', '-', $receiptNumber);
        $filename = "Kwitansi-{$payment->id}-{$cleanReceiptNo}.pdf";

        $brandName = $settings->brand_name ?: 'VexaHost';
        $invoice = $project?->latestInvoice ?: $project?->invoices()->first();
        $receiptUrl = $invoice?->payment_token ? route('invoices.pay', $invoice->payment_token) : null;
        $linkText = $receiptUrl ? "\n\n📄 *Lihat Kwitansi Digital (Tanpa Login):*\n👉 {$receiptUrl}" : "";
        $clientName = $lead->nama_kontak ?: $lead->nama_usaha;

        $caption = "Halo Kak *{$clientName}*, terima kasih! 🙏\n\n"
                 . "Pembayaran sebesar *Rp " . number_format($payment->jumlah, 0, ',', '.') . "* untuk *{$project->nama_project}* telah kami terima dan diverifikasi sah.\n\n"
                 . "🧾 *No. Kwitansi:* {$receiptNumber}\n"
                 . "📅 *Tanggal:* {$data['receiptDate']}\n"
                 . "💵 *Jumlah:* Rp " . number_format($payment->jumlah, 0, ',', '.') . " ({$terbilang})\n\n"
                 . "Terlampir bukti Kwitansi resmi {$brandName}.{$linkText}\n\nTerima kasih atas kerja samanya! 🙏✨";

        $res = $waService->sendMediaWhatsApp(
            to: $lead->kontak_wa,
            fileContent: $pdfContent,
            filename: $filename,
            caption: $caption,
            lead: $lead,
            tipePesan: 'kwitansi_pdf'
        );

        if ($res['success'] ?? false) {
            ActivityLogger::log('send_receipt_wa', "Mengirim kwitansi PDF {$receiptNumber} via WA ke {$lead->kontak_wa}", 'Payment', $payment->id);
            return back()->with('success', "🧾 Dokumen PDF Kwitansi {$receiptNumber} berhasil dikirim ke WhatsApp {$lead->kontak_wa}!");
        }

        return back()->with('error', "Gagal mengirim PDF Kwitansi via WhatsApp: " . ($res['message'] ?? 'Terjadi kesalahan'));
    }

    /**
     * Dispatch official PDF Kwitansi (Payment Receipt) to client via Email.
     */
    public function sendPaymentReceiptEmail(Request $request, Payment $payment)
    {
        $payment->load('project.lead');
        $project = $payment->project;
        $lead = $project?->lead;

        if (!$lead || empty($lead->email)) {
            return back()->with('error', 'Alamat email klien tidak ditemukan pada data Lead/Proyek.');
        }

        $receiptNumber = 'KW/' . ($payment->tanggal ? $payment->tanggal->format('Ym') : now()->format('Ym')) . '/' . str_pad($payment->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($payment->jumlah) . ' Rupiah';
        $settings = CompanySetting::get();

        $data = [
            'payment' => $payment,
            'project' => $project,
            'lead' => $lead,
            'receiptNumber' => $receiptNumber,
            'receiptDate' => $payment->tanggal ? $payment->tanggal->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            'terbilang' => $terbilang,
            'logoBase64' => $settings->logo_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.receipt', $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();
        $cleanReceiptNo = str_replace('/', '-', $receiptNumber);
        $filename = "Kwitansi-{$payment->id}-{$cleanReceiptNo}.pdf";

        $invoice = $project?->latestInvoice ?: $project?->invoices()->first();
        $receiptUrl = $invoice?->payment_token ? route('invoices.pay', $invoice->payment_token) : null;

        try {
            Mail::to($lead->email)->send(
                new PaymentReceiptMail($payment, $invoice, $settings, $pdfContent, $filename, $receiptNumber, $receiptUrl)
            );

            ActivityLogger::log('send_receipt_email', "Mengirim kwitansi PDF {$receiptNumber} via Email ke {$lead->email}", 'Payment', $payment->id);
            return back()->with('success', "✉️ Dokumen PDF Kwitansi {$receiptNumber} berhasil dikirim ke email {$lead->email}!");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal mengirim Email Kwitansi: " . $e->getMessage());
        }
    }

    /**
     * Generate and dispatch official PDF Maintenance Invoice to client via WhatsApp Gateway.
     */
    public function sendMaintenanceInvoiceWa(Request $request, MaintenanceSubscription $subscription, \App\Services\WhatsApp\WhatsAppService $waService)
    {
        $subscription->load(['lead', 'project']);
        $lead = $subscription->lead;

        if (!$lead || empty($lead->kontak_wa)) {
            return back()->with('error', 'Nomor WhatsApp klien tidak ditemukan.');
        }

        $invoice = $subscription->getOrCreateInvoice();
        if (empty($invoice->payment_token)) {
            $invoice->payment_token = Str::random(40);
            $invoice->saveQuietly();
        }
        $directPayUrl = route('invoices.pay', $invoice->payment_token);

        $invoiceNumber = $invoice->invoice_number;
        $dueDate = $subscription->tanggal_jatuh_tempo_berikutnya ? $subscription->tanggal_jatuh_tempo_berikutnya->translatedFormat('d F Y') : now()->addDays(7)->translatedFormat('d F Y');
        $settings = CompanySetting::get();

        $data = [
            'subscription' => $subscription,
            'lead' => $lead,
            'project' => $subscription->project,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => now()->translatedFormat('d F Y'),
            'dueDate' => $dueDate,
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.maintenance', $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();
        $cleanMntNo = str_replace('/', '-', $invoiceNumber);
        $filename = "Invoice-Maintenance-{$subscription->id}-{$cleanMntNo}.pdf";

        $brandName = $settings->brand_name ?: 'VexaHost';
        $clientName = $lead->nama_kontak ?: $lead->nama_usaha;
        $caption = "Halo Kak *{$clientName}*, terlampir Invoice Pemeliharaan (Maintenance) Website & Server untuk periode berikutnya.\n\n"
                 . "📄 *No. Invoice:* {$invoiceNumber}\n"
                 . "💰 *Biaya Bulanan:* Rp " . number_format($subscription->harga_bulanan, 0, ',', '.') . "\n"
                 . "⏰ *Jatuh Tempo:* {$dueDate}\n\n"
                 . "⚡ *Link Pembayaran Langsung (Tanpa Perlu Login):*\n"
                 . "👉 {$directPayUrl}\n\n"
                 . "✨ *Metode Otomatis (Instan 24 Jam):*\n"
                 . "• QRIS Semua Bank & E-Wallet (BCA, Livin, GoPay, OVO, Dana)\n"
                 . "• Virtual Account Bank Mandiri & BNI\n\n"
                 . "Kwitansi pelunasan resmi akan terbit otomatis setelah pembayaran berhasil tanpa perlu konfirmasi manual. Terima kasih! 🙏\n- {$brandName}";

        $res = $waService->sendMediaWhatsApp(
            to: $lead->kontak_wa,
            fileContent: $pdfContent,
            filename: $filename,
            caption: $caption,
            lead: $lead,
            tipePesan: 'maintenance_pdf'
        );

        if ($res['success'] ?? false) {
            ActivityLogger::log('send_maintenance_invoice_wa', "Mengirim invoice maintenance PDF {$invoiceNumber} via WA ke {$lead->kontak_wa}", 'MaintenanceSubscription', $subscription->id);
            return back()->with('success', "📄 Dokumen PDF Invoice Maintenance {$invoiceNumber} berhasil dikirim ke WhatsApp {$lead->kontak_wa}!");
        }

        return back()->with('error', "Gagal mengirim PDF Invoice Maintenance: " . ($res['message'] ?? 'Terjadi kesalahan'));
    }

    /**
     * Dispatch official PDF Maintenance Invoice to client via Email.
     */
    public function sendMaintenanceInvoiceEmail(Request $request, MaintenanceSubscription $subscription)
    {
        $subscription->load(['lead', 'project']);
        $lead = $subscription->lead;

        if (!$lead || empty($lead->email)) {
            return back()->with('error', 'Alamat email klien tidak ditemukan pada data Maintenance.');
        }

        $invoice = $subscription->getOrCreateInvoice();
        if (empty($invoice->payment_token)) {
            $invoice->payment_token = Str::random(40);
            $invoice->saveQuietly();
        }
        $directPayUrl = route('invoices.pay', $invoice->payment_token);

        $invoiceNumber = $invoice->invoice_number;
        $dueDate = $subscription->tanggal_jatuh_tempo_berikutnya ? $subscription->tanggal_jatuh_tempo_berikutnya->translatedFormat('d F Y') : now()->addDays(7)->translatedFormat('d F Y');
        $settings = CompanySetting::get();

        $data = [
            'subscription' => $subscription,
            'lead' => $lead,
            'project' => $subscription->project,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => now()->translatedFormat('d F Y'),
            'dueDate' => $dueDate,
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.maintenance', $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();
        $cleanMntNo = str_replace('/', '-', $invoiceNumber);
        $filename = "Invoice-Maintenance-{$subscription->id}-{$cleanMntNo}.pdf";

        try {
            Mail::to($lead->email)->send(
                new MaintenanceInvoiceMail($subscription, $invoice, $settings, $pdfContent, $filename, $directPayUrl, $invoiceNumber, $dueDate)
            );

            ActivityLogger::log('send_maintenance_invoice_email', "Mengirim invoice maintenance PDF {$invoiceNumber} via Email ke {$lead->email}", 'MaintenanceSubscription', $subscription->id);
            return back()->with('success', "✉️ Dokumen PDF Invoice Maintenance {$invoiceNumber} berhasil dikirim ke email {$lead->email}!");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal mengirim Email Invoice Maintenance: " . $e->getMessage());
        }
    }

    /**
     * Show Project Settlement Invoice (HTML, PDF, Word).
     */
    public function settlementInvoice(Request $request, Project $project)
    {
        app(ProjectLifecycleService::class)->syncInvoiceForProject($project);
        $project->load(['lead', 'payments', 'latestInvoice']);

        $settlementNumber = 'INV-SETTLE/' . ($project->created_at ? $project->created_at->format('Ym') : now()->format('Ym')) . '/' . str_pad($project->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($project->remaining_balance) . ' Rupiah';

        ActivityLogger::log('view_settlement_invoice', "Melihat/mencetak dokumen tagihan pelunasan {$settlementNumber} untuk project {$project->nama_project}", 'Project', $project->id);

        $settings = CompanySetting::get();

        $data = [
            'project' => $project,
            'lead' => $project->lead,
            'settlementNumber' => $settlementNumber,
            'settlementDate' => now()->translatedFormat('d F Y'),
            'dueDate' => now()->addDays(5)->translatedFormat('d F Y'),
            'terbilang' => $terbilang,
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => false,
        ];

        $cleanSettlementNo = str_replace('/', '-', $settlementNumber);
        $format = strtolower($request->get('format', 'html'));
        if ($format === 'pdf') {
            $data['isPdf'] = true;
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.settlement', $data)->setPaper('a4', 'portrait');
            return $pdf->download("Tagihan-Pelunasan-{$cleanSettlementNo}.pdf");
        }
        if ($format === 'word' || $format === 'doc') {
            $data['isPdf'] = true;
            $html = view('admin.invoices.settlement', $data)->render();
            return response($html, 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"Tagihan-Pelunasan-{$cleanSettlementNo}.doc\"",
            ]);
        }

        return view('admin.invoices.settlement', $data);
    }

    /**
     * Generate and dispatch official PDF Settlement Invoice to client via WhatsApp Gateway.
     */
    public function sendSettlementInvoiceWa(Request $request, Project $project, \App\Services\WhatsApp\WhatsAppService $waService)
    {
        $project->load(['lead', 'payments', 'invoices']);
        $lead = $project->lead;
        if (!$lead || empty($lead->kontak_wa)) {
            return back()->with('error', 'Nomor WhatsApp klien tidak ditemukan.');
        }

        if ($project->remaining_balance <= 0) {
            return back()->with('info', "Proyek {$project->nama_project} sudah lunas, tidak ada sisa tagihan pelunasan.");
        }

        $settlementNumber = 'INV-SETTLE/' . ($project->created_at ? $project->created_at->format('Ym') : now()->format('Ym')) . '/' . str_pad($project->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($project->remaining_balance) . ' Rupiah';
        $settings = CompanySetting::get();

        $data = [
            'project' => $project,
            'lead' => $lead,
            'settlementNumber' => $settlementNumber,
            'settlementDate' => now()->translatedFormat('d F Y'),
            'dueDate' => now()->addDays(5)->translatedFormat('d F Y'),
            'terbilang' => $terbilang,
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.settlement', $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();
        $cleanSettlementNo = str_replace('/', '-', $settlementNumber);
        $filename = "Tagihan-Pelunasan-{$project->id}-{$cleanSettlementNo}.pdf";

        $brandName = $settings->brand_name ?: 'VexaHost';
        
        $invoice = $project->latestInvoice ?: $project->invoices()->first();
        if (!$invoice) {
            $invoice = app(ProjectLifecycleService::class)->syncInvoiceForProject($project);
        }
        if (empty($invoice->payment_token)) {
            $invoice->payment_token = Str::random(40);
            $invoice->saveQuietly();
        }
        $directPayUrl = route('invoices.pay', $invoice->payment_token);
        $clientName = $lead->nama_kontak ?: $lead->nama_usaha;

        $caption = "Halo Kak *{$clientName}*, terlampir dokumen resmi *Invoice Tagihan Pelunasan* untuk proyek *{$project->nama_project}*.\n\n"
                 . "📄 *No. Dokumen:* {$settlementNumber}\n"
                 . "💰 *Total Nilai Proyek:* Rp " . number_format($project->harga, 0, ',', '.') . "\n"
                 . "💵 *DP Diterima:* Rp " . number_format($project->total_paid, 0, ',', '.') . "\n"
                 . "💳 *Sisa Tagihan Pelunasan:* Rp " . number_format($project->remaining_balance, 0, ',', '.') . "\n\n"
                 . "⚡ *Link Pelunasan Langsung (Tanpa Perlu Login):*\n"
                 . "👉 {$directPayUrl}\n\n"
                 . "✨ *Metode Otomatis (Instan 24 Jam):*\n"
                 . "• QRIS Semua Bank & E-Wallet (BCA, Livin, GoPay, OVO, Dana)\n"
                 . "• Virtual Account Bank Mandiri & BNI\n\n"
                 . "Kwitansi lunas bertanda tangan digital terbit seketika tanpa perlu konfirmasi manual. Terima kasih banyak atas kerjasamanya! 🚀\n- {$brandName}";

        $res = $waService->sendMediaWhatsApp(
            to: $lead->kontak_wa,
            fileContent: $pdfContent,
            filename: $filename,
            caption: $caption,
            lead: $lead,
            tipePesan: 'settlement_invoice_pdf'
        );

        if ($res['success'] ?? false) {
            ActivityLogger::log('send_settlement_wa', "Mengirim dokumen PDF tagihan pelunasan {$settlementNumber} via WA ke {$lead->kontak_wa}", 'Project', $project->id);
            return back()->with('success', "📄 Dokumen PDF Tagihan Pelunasan {$settlementNumber} berhasil dikirim ke WhatsApp {$lead->kontak_wa}!");
        }

        return back()->with('error', "Gagal mengirim PDF Tagihan Pelunasan: " . ($res['message'] ?? 'Terjadi kesalahan'));
    }

    /**
     * Dispatch official PDF Settlement Invoice to client via Email.
     */
    public function sendSettlementInvoiceEmail(Request $request, Project $project)
    {
        $project->load(['lead', 'payments', 'invoices']);
        $lead = $project->lead;
        if (!$lead || empty($lead->email)) {
            return back()->with('error', 'Alamat email klien tidak ditemukan pada data Lead/Proyek.');
        }

        if ($project->remaining_balance <= 0) {
            return back()->with('info', "Proyek {$project->nama_project} sudah lunas, tidak ada sisa tagihan pelunasan.");
        }

        $settlementNumber = 'INV-SETTLE/' . ($project->created_at ? $project->created_at->format('Ym') : now()->format('Ym')) . '/' . str_pad($project->id, 4, '0', STR_PAD_LEFT);
        $terbilang = $this->terbilang($project->remaining_balance) . ' Rupiah';
        $settings = CompanySetting::get();

        $data = [
            'project' => $project,
            'lead' => $lead,
            'settlementNumber' => $settlementNumber,
            'settlementDate' => now()->translatedFormat('d F Y'),
            'dueDate' => now()->addDays(5)->translatedFormat('d F Y'),
            'terbilang' => $terbilang,
            'bankInfo' => $settings->bank_info_string,
            'qrisBase64' => $settings->qris_base64,
            'logoBase64' => $settings->logo_base64,
            'signatureBase64' => $settings->signature_base64,
            'settings' => $settings,
            'isPdf' => true,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.settlement', $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();
        $cleanSettlementNo = str_replace('/', '-', $settlementNumber);
        $filename = "Tagihan-Pelunasan-{$project->id}-{$cleanSettlementNo}.pdf";

        $invoice = $project->latestInvoice ?: $project->invoices()->first();
        if (!$invoice) {
            $invoice = app(ProjectLifecycleService::class)->syncInvoiceForProject($project);
        }
        if (empty($invoice->payment_token)) {
            $invoice->payment_token = Str::random(40);
            $invoice->saveQuietly();
        }
        $directPayUrl = route('invoices.pay', $invoice->payment_token);

        try {
            Mail::to($lead->email)->send(
                new SettlementInvoiceMail($project, $invoice, $settings, $pdfContent, $filename, $directPayUrl, $settlementNumber)
            );

            ActivityLogger::log('send_settlement_email', "Mengirim tagihan pelunasan PDF {$settlementNumber} via Email ke {$lead->email}", 'Project', $project->id);
            return back()->with('success', "✉️ Dokumen PDF Tagihan Pelunasan {$settlementNumber} berhasil dikirim ke email {$lead->email}!");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal mengirim Email Tagihan Pelunasan: " . $e->getMessage());
        }
    }
}
