<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Gel\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TaskEscalationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // 1. Détecter les tâches urgentes qui ont dépassé leur date d'échéance depuis plus de 24h
        $overdueTasks = Task::whereIn('statut', ['a_faire', 'en_cours'])
            ->whereIn('priorite', ['haute', 'critique'])
            ->whereNotNull('date_echeance')
            ->where('date_echeance', '<', now()->subDays(1)->toDateString())
            ->with(['assigne', 'cabinet'])
            ->get();

        foreach ($overdueTasks as $task) {
            // Notifier le responsable (l'utilisateur assigné)
            if ($task->assigned_to) {
                Notification::create([
                    'user_id' => $task->assigned_to,
                    'type' => 'error',
                    'title' => 'Tâche Urgente en retard !',
                    'message' => "La tâche \"{$task->titre}\" a dépassé son échéance. Veuillez la traiter en priorité.",
                    'data' => ['url' => route('gel-secretary.tasks.index')]
                ]);
            }
            
            // Escalade : trouver les admins du cabinet pour les notifier
            $admins = User::where('cabinet_id', $task->cabinet_id)
                ->where('role', 'admin') // Rôle arbitraire, ou gestion des permissions
                ->get();
                
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'warning',
                    'title' => 'Escalade : Tâche en retard',
                    'message' => "La tâche \"{$task->titre}\" assignée à " . ($task->assigne->name ?? 'Inconnu') . " est en retard. Intervention requise.",
                    'data' => ['url' => route('gel-secretary.tasks.index')]
                ]);
            }
        }
    }
}
