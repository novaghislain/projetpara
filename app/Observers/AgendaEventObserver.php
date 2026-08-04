<?php

namespace App\Observers;

use App\Models\Dae\DaeAgendaEvent;
use App\Models\Notification;

class AgendaEventObserver
{
    /**
     * Handle the DaeAgendaEvent "updated" event.
     */
    public function updated(DaeAgendaEvent $event): void
    {
        // Si l'événement vient de passer au statut "terminé"
        if ($event->isDirty('status') && $event->status === 'completed') {
            
            // Envoyer une notification pour proposer la création de tâches de suivi
            if ($event->user_id) {
                Notification::create([
                    'user_id' => $event->user_id,
                    'type' => 'info',
                    'title' => 'Rendez-vous terminé',
                    'message' => "Le rendez-vous \"{$event->title}\" est terminé. Voulez-vous créer des tâches de suivi ?",
                    'data' => [
                        'url' => route('gel-secretary.tasks.index') . '?from_event=' . $event->id
                    ]
                ]);
            }
        }
    }
}
