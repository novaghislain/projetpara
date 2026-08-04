<?php

namespace App\Mail;

use App\Models\Dae\DaeAgendaEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgendaInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public DaeAgendaEvent $event;
    public string $cabinetName;

    public function __construct(DaeAgendaEvent $event, string $cabinetName)
    {
        $this->event = $event;
        $this->cabinetName = $cabinetName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📅 Invitation : ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.agenda-invitation',
        );
    }
}
