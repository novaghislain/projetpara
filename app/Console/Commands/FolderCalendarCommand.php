<?php

namespace App\Console\Commands;

use App\Models\ClientFolder;
use App\Models\FolderRolloverLog;
use App\Models\User;
use App\Notifications\RealTimeNotification;
use App\Services\FolderStructureService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * S2 — Basculement automatique des mois/années de l'Espace Documentaire.
 *
 * Idempotent et redondant : exécuté plusieurs fois par jour, il garantit que le
 * mois (et l'année) en cours existent, sans jamais dépendre d'une action
 * manuelle. Une erreur nocturne est auto-réparée à la passe suivante et
 * déclenche une alerte (notification + log).
 */
class FolderCalendarCommand extends Command
{
    protected $signature = 'folders:calendar
        {--for= : Date forcée YYYY-MM-DD pour tester le basculement}
        {--dry-run : Compare sans écrire (aucune création, aucun log)}';

    protected $description = 'Crée/garantit automatiquement le mois et l\'année en cours pour toutes les entreprises (Espace Documentaire).';

    public function handle(FolderStructureService $structure): int
    {
        $date = $this->option('for')
            ? Carbon::parse($this->option('for'))
            : Carbon::now();

        $dry = (bool) $this->option('dry-run');
        $runId = date('Ymd-His') . '-' . substr(uniqid(), -5);

        $this->info('[' . $date->format('Y-m-d') . '] Périmètre(s) de l\'espace documentaire…');

        $scopes = ClientFolder::query()
            ->whereNotNull('client_id')
            ->orWhere(function ($q) {
                $q->whereNotNull('user_id');
            })
            ->selectRaw('client_id, user_id')
            ->distinct()
            ->get()
            ->map(fn ($s) => ['client_id' => $s->client_id, 'user_id' => $s->user_id])
            ->all();

        if (empty($scopes)) {
            $this->warn('Aucun espace documentaire actif. Rien à faire.');
            return self::SUCCESS;
        }

        $failed = [];

        foreach ($scopes as $scope) {
            $clientId = $scope['client_id'];
            $userId = $scope['user_id'];

            $monthAlreadyThere = $this->monthFolderExists($clientId, $userId, $date->year, $date->month);

            try {
                if ($dry) {
                    $this->line(($monthAlreadyThere ? '[ok] ' : '[CREATED] ') . $this->scopeLabel($scope) . ' → ' . $date->format('m/Y'));
                    continue;
                }

                $structure->ensureCanonical($clientId, $userId, $date);

                FolderRolloverLog::create([
                    'client_id'   => $clientId,
                    'user_id'     => $userId,
                    'scope_label' => $this->scopeLabel($scope),
                    'year'        => $date->year,
                    'month'       => $date->month,
                    'status'      => $monthAlreadyThere ? 'ok' : 'created',
                    'note'        => $monthAlreadyThere
                        ? 'Mois déjà en place (idempotent).'
                        : 'Mois ' . $date->format('m/Y') . ' créé automatiquement (+6 sous-dossiers).',
                    'run_id'      => $runId,
                ]);

                $this->info(($monthAlreadyThere ? '  ok  ' : '  crÉ ') . $this->scopeLabel($scope) . ' → ' . $date->format('m/Y'));
            } catch (\Throwable $e) {
                $failed[$this->scopeLabel($scope)] = $e->getMessage();

                if (!$dry) {
                    FolderRolloverLog::create([
                        'client_id'   => $clientId,
                        'user_id'     => $userId,
                        'scope_label' => $this->scopeLabel($scope),
                        'year'        => $date->year,
                        'month'       => $date->month,
                        'status'      => 'failure',
                        'note'        => mb_substr($e->getMessage(), 0, 500),
                        'run_id'      => $runId,
                    ]);
                }
            }
        }

        if (!empty($failed)) {
            $this->alert('Échec de création de mois pour : ' . implode(', ', array_keys($failed)));
            $this->notifyAlert($date);

            return self::FAILURE;
        }

        // Préparer l'année suivante si une bascule d'année est imminente/détectée
        if ($this->option('for') && $date->month === 1 && $date->day === 1) {
            $this->info('Nouvel an détecté — le mois de Janvier vient d’être créé pour chaque espace.');
        }

        return self::SUCCESS;
    }

    /**
     * Un mois (format MM_Mois) existe déjà pour la période ?
     * Lecture seule (aucune création) — requis pour un --dry-run strict.
     */
    protected function monthFolderExists(?int $clientId, ?int $userId, int $year, int $month): bool
    {
        // « Courant » est la racine de niveau 1 du calendrier (plus aucun
        // enveloppeur « Documents » intermédiaire).
        $courant = ClientFolder::forClientOrUser($clientId, $userId)
            ->whereNull('parent_id')
            ->where('name', FolderStructureService::COURANT_ROOT_NAME)
            ->first();

        if (!$courant) {
            return false;
        }

        $yearFolder = ClientFolder::forClientOrUser($clientId, $userId)
            ->where('parent_id', $courant->id)
            ->where('name', (string) $year)
            ->first();

        if (!$yearFolder) {
            return false;
        }

        return ClientFolder::forClientOrUser($clientId, $userId)
            ->where('parent_id', $yearFolder->id)
            ->where('name', (new FolderStructureService())->monthName($month, $year))
            ->exists();
    }

    protected function scopeLabel(array $scope): string
    {
        return $scope['client_id'] ? 'client:'.$scope['client_id'] : 'user:'.$scope['user_id'];
    }

    /**
     * Alerte humaine si le job échoue : notifications aux secrétaires/
     * gestionnaires du périmètre concerné + journal d'audit.
     */
    protected function notifyAlert(Carbon $date): void
    {
        try {
            $recipients = User::where('is_suspended', false)
                ->where(function ($q) {
                    $q->whereNull('workspace_type')
                      ->orWhereIn('workspace_type', ['gel_pool', 'secretaire_independant', 'individuel']);
                })
                ->limit(50)
                ->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new RealTimeNotification(
                    'Espace documentaire — basculement de période',
                    "Le mois {$date->format('m/Y')} n'a pas pu être généré automatiquement. Merci de vérifier.",
                    route('gel-secretary.documents.index'),
                    'fas fa-exclamation-triangle'));
                }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('folders:calendar : alerte échouée — ' . $e->getMessage());
        }
    }
}
