<?php

namespace App\Mail;

use App\Models\Gel\ClientInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable d'invitation d'un collaborateur (comptable / secrétaire).
 *
 * Envoyé par l'entreprise à un collaborateur invité pour rejoindre
 * son espace. Le lien d'acceptation est valable tant que l'invitation
 * n'est pas expirée (7 jours par défaut).
 */
class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * L'invitation concernée.
     *
     * @var ClientInvitation
     */
    public $invitation;

    /**
     * Crée une nouvelle instance de message.
     */
    public function __construct(ClientInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Obtenir l'enveloppe du message.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vous avez été invité à rejoindre une entreprise sur GEL',
        );
    }

    /**
     * Obtenir la définition du contenu du message.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
        );
    }
}
