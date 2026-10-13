<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public ?string $customMessage = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];
        if (!empty($notifiable->email) && config('mail.default') === 'smtp') {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $statusLabel = app(\App\Services\TicketWorkflowService::class)
            ->statusLabel($this->ticket->status);
        $url = route('tickets.index');
        $support = \App\Support\MailIdentity::support();
        $replyTo = \App\Support\MailIdentity::replyTo();

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->from($support['address'], $support['name'])
            ->replyTo($replyTo['address'], $replyTo['name'])
            ->subject('[UPDATE TIKET #' . $this->ticket->id . '] ' . $this->ticket->title)
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Ada pembaruan status pada tiket Anda: **' . $this->ticket->title . '**')
            ->line('**Status Terkini:** ' . $statusLabel)
            ->when($this->customMessage, fn ($mail) => $mail->line('**Catatan:** ' . $this->customMessage))
            ->action('Lihat Detail Tiket', $url)
            ->line('Terima kasih telah mempercayakan kebutuhan Anda kepada VexaHost.')
            ->salutation('Salam hormat, Tim Support VexaHost');
    }

    protected function payload(object $notifiable): array
    {
        $this->ticket->loadMissing(['technician', 'project']);

        $statusLabel = app(\App\Services\TicketWorkflowService::class)
            ->statusLabel($this->ticket->status);

        $message = $this->customMessage
            ?? ('Status tiket "' . $this->ticket->title . '" sekarang: ' . $statusLabel);

        $isClient = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('client');

        if ($this->ticket->technician && $isClient) {
            $message .= ' — PIC: ' . $this->ticket->technician->name;
        }

        if ($this->ticket->project && $isClient) {
            $message .= ' (Proyek: ' . $this->ticket->project->name . ')';
        }

        return [
            'ticket_id' => $this->ticket->id,
            'project_id' => $this->ticket->project?->id,
            'title' => $this->ticket->title,
            'status' => $this->ticket->status,
            'status_label' => $statusLabel,
            'technician_name' => $this->ticket->technician?->name,
            'project_name' => $this->ticket->project?->name,
            'url' => $isClient
                ? route('tickets.index')
                : ($this->ticket->project
                    ? route('projects.show', $this->ticket->project)
                    : route('technician.tickets')),
            'message' => $message,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload($notifiable);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload($notifiable));
    }
}
