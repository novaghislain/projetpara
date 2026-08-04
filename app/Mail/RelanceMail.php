<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RelanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clientName;
    public string $reference;
    public ?string $amount;
    public string $dueDate;
    public string $type;

    /**
     * @param array $data Keys: type, clientName, reference, amount, dueDate
     */
    public function __construct(array $data)
    {
        $this->type       = $data['type'];
        $this->clientName = $data['clientName'];
        $this->reference  = $data['reference'];
        $this->amount     = $data['amount'] ?? null;
        $this->dueDate    = $data['dueDate'];
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'invoice'
            ? "Rappel de paiement – Facture {$this->reference}"
            : "Rappel – Action requise : {$this->reference}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.relance');
    }

    public function attachments(): array
    {
        return [];
    }
}
