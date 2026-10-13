<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            return ['mail'];
        }

        $channels = ['database', 'broadcast'];
        // Send email if mailer is configured for smtp
        if (!empty($notifiable->email) && config('mail.default') === 'smtp') {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('client')
            ? route('tickets.index')
            : route('technician.tickets');

        $greetingName = $notifiable->name ?? 'Tim Support VexaHost';
        $noreply = \App\Support\MailIdentity::noreply();
        $replyTo = \App\Support\MailIdentity::replyTo();

        return (new MailMessage)
            ->from($noreply['address'], $noreply['name'])
            ->replyTo($replyTo['address'], $replyTo['name'])
            ->subject('[TIKET BARU #' . $this->ticket->id . '] ' . $this->ticket->title)
            ->greeting('Halo, ' . $greetingName . '!')
            ->line('Ada tiket baru masuk dari klien: **' . ($this->ticket->client?->name ?? 'Klien') . '**')
            ->line('**Judul:** ' . $this->ticket->title)
            ->line('**Prioritas:** ' . strtoupper($this->ticket->priority))
            ->line('**Deskripsi:** ' . $this->ticket->description)
            ->action('Lihat & Tangani Tiket', $url)
            ->line('Mohon segera ditindaklanjuti sesuai target SLA.')
            ->salutation('Salam, VexaHost System');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'priority' => $this->ticket->priority,
            'client_name' => $this->ticket->client->name,
            'url' => method_exists($notifiable, 'hasRole') && $notifiable->hasRole('client')
                ? route('tickets.index')
                : route('technician.tickets'),
            'message' => 'Tiket baru masuk: ' . $this->ticket->title,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
