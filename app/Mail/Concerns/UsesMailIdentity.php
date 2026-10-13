<?php

namespace App\Mail\Concerns;

use App\Support\MailIdentity;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

trait UsesMailIdentity
{
    /**
     * Buat instance Envelope dengan pengirim eksplisit dan Reply-To seragam.
     *
     * @param string $subject
     * @param string $senderType 'noreply' atau 'support'
     * @param array $tags
     * @param string|null $using
     * @return Envelope
     */
    protected function makeEnvelope(
        string $subject,
        string $senderType = 'noreply',
        array $tags = [],
        ?string $using = null
    ): Envelope {
        $sender = $senderType === 'support' ? MailIdentity::support() : MailIdentity::noreply();
        $replyTo = MailIdentity::replyTo();

        return new Envelope(
            from: new Address($sender['address'], $sender['name']),
            replyTo: [new Address($replyTo['address'], $replyTo['name'])],
            subject: $subject,
            tags: $tags,
            using: $using ? [$using] : [],
        );
    }
}
