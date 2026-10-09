<?php

namespace App\Mail;

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\MaintenanceSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MaintenanceSubscription $subscription,
        public Invoice $invoice,
        public CompanySetting $settings,
        public string $pdfContent,
        public string $filename,
        public string $directPaymentUrl,
        public string $invoiceNumber,
        public string $dueDate
    ) {}

    public function envelope(): Envelope
    {
        $brand = $this->settings->brand_name ?: 'VexaHost';
        $clientName = $this->subscription->lead?->nama_usaha ?: ($this->subscription->project?->name ?: 'Website');
        return new Envelope(
            subject: "[Tagihan Maintenance] Invoice #{$this->invoiceNumber} - {$clientName} ({$brand})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.maintenance',
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
