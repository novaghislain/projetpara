<?php

namespace App\Notifications;

use App\Models\Gel\ConsultantMission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ConsultantInvitationNotification extends Notification
{
    use Queueable;

    public ConsultantMission $mission;
    public string $acceptUrl;

    public function __construct(ConsultantMission $mission, string $acceptUrl)
    {
        $this->mission = $mission;
        $this->acceptUrl = $acceptUrl;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mission = $this->mission;
        $entreprise = $mission->entreprise?->nom ?? 'GEL SABINET';

        return (new MailMessage)
            ->subject('[GEL SABINET] Invitation à intervenir sur le dossier : ' . $mission->title)
            ->greeting('Bonjour,')
            ->line('Vous avez été invité(e) à intervenir en tant que **' . $mission->specialty . '** sur le dossier suivant :')
            ->line('**Entreprise :** ' . $entreprise)
            ->line('**Dossier :** ' . $mission->title)
            ->line('**Périmètre :** ' . $mission->description)
            ->line('**Votre accès est valable jusqu\'au :** ' . $mission->end_date->format('d/m/Y'))
            ->action('Accepter l\'invitation et accéder au dossier', $this->acceptUrl)
            ->line('Ce lien est à usage unique et expire dans 72 heures.')
            ->line('Si vous n\'êtes pas à l\'origine de cette invitation, ignorez simplement ce message.')
            ->salutation('Cordialement, L\'équipe GEL SABINET');
    }
}
