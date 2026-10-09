<?php

namespace App\Mail;

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public ?Invoice $invoice,
        public CompanySetting $settings,
        public string $pdfContent,
        public string $filename,
        public string $receiptNumber,
        public ?string $receiptUrl = null
    ) {}

    public function envelope(): Envelope
    {
        $brand = $this->settings->brand_name ?: 'VexaHost';
        return new Envelope(
            subject: "[Kwitansi Resmi] Bukti Pembayaran Sah #{$this->receiptNumber} ({$brand})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.receipt',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
