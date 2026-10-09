<?php

namespace App\Mail;

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public Invoice $invoice,
        public CompanySetting $settings,
        public string $pdfContent,
        public string $filename,
        public string $directPaymentUrl
    ) {}

    public function envelope(): Envelope
    {
        $brand = $this->settings->brand_name ?: 'VexaHost';
        return new Envelope(
            subject: "[Tagihan Resmi] Invoice #{$this->invoice->invoice_number} - {$this->project->nama_project} ({$brand})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.project',
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
