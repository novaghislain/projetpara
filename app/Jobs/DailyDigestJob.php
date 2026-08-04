<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use App\Models\Gel\Task;
use App\Models\Dae\DaeAgendaEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DailyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // On cible les utilisateurs ayant le rôle secrétaire ou associés (ici on boucle sur tous pour la démo)
        $users = User::all();

        foreach ($users as $user) {
            $cabinetId = $user->cabinet_id;
            if (!$cabinetId) continue;

            // 1. Tâches urgentes/en retard du jour pour l'utilisateur
            $tasks = Task::where('assigned_to', $user->id)
                ->whereIn('statut', ['a_faire', 'en_cours'])
                ->whereDate('date_echeance', '<=', now()->toDateString())
                ->count();

            // 2. RDV du jour
            $events = DaeAgendaEvent::where('user_id', $user->id)
                ->whereDate('start_at', now()->toDateString())
                ->count();

            // 3. Nouveaux courriers non traités
            $courriers = \App\Models\Dae\DaeCourrier::whereHas('client', function($q) use ($cabinetId) {
                    $q->where('cabinet_id', $cabinetId);
                })
                ->where('statut', 'non_traite')
                ->count();

            if ($tasks == 0 && $events == 0 && $courriers == 0) {
                continue; // Rien de spécial aujourd'hui
            }

            $message = "Voici vos priorités du jour :\n";
            if ($tasks > 0) $message .= "- $tasks tâche(s) urgente(s) ou en retard\n";
            if ($events > 0) $message .= "- $events rendez-vous prévu(s) aujourd'hui\n";
            if ($courriers > 0) $message .= "- $courriers courrier(s) en attente de traitement\n";
            $message .= "\nPassez une excellente journée !";

            Notification::create([
                'user_id' => $user->id,
                'type' => 'info',
                'title' => 'Digest : Vos priorités du jour',
                'message' => $message,
                'data' => ['url' => route('gel-secretary.dashboard')]
            ]);
        }
    }
}
