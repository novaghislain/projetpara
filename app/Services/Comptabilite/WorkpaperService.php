<?php

namespace App\Services\Comptabilite;

use App\Models\Client;
use App\Models\Workpaper;
use App\Models\AccountingAccount;
use App\Models\JournalEntry;
use App\Models\FiscalYear;
use Illuminate\Support\Facades\DB;

class WorkpaperService
{
    /**
     * Initialise ou récupère les Workpapers (Dossiers de révision) pour une période donnée.
     * Cette méthode garantit qu'il y a une ligne de Workpaper pour chaque compte ayant un solde.
     */
    public function getOrInitializeWorkpapers(Client $client, FiscalYear $fiscalYear, string $period)
    {
        // 1. Récupérer tous les comptes actifs du client
        $accounts = AccountingAccount::where('client_id', $client->id)->get();
        
        $workpapers = [];

        DB::transaction(function () use ($client, $fiscalYear, $period, $accounts, &$workpapers) {
            foreach ($accounts as $account) {
                // Créer ou récupérer le Workpaper pour ce compte
                $workpaper = Workpaper::firstOrCreate(
                    [
                        'client_id' => $client->id,
                        'fiscal_year_id' => $fiscalYear->id,
                        'period' => $period,
                        'account_id' => $account->id,
                    ],
                    [
                        'status' => 'pending',
                        'notes' => null,
                        'adjustments' => null,
                        'attachments' => null,
                    ]
                );

                // On attache le modèle account complet pour le frontend
                $workpaper->setRelation('account', $account);
                
                // Calculer les soldes fictifs (N et N-1)
                $workpaper->balance_n = $this->calculateAccountBalance($account, $fiscalYear);
                $workpaper->balance_n_minus_1 = $this->calculatePreviousYearBalance($account, $fiscalYear);
                
                // Détection de variance (IA-Native : Anomalie pré-signalée)
                $workpaper->ai_variance_flag = $this->detectAnomalousVariance($workpaper->balance_n, $workpaper->balance_n_minus_1);
                
                $workpapers[] = $workpaper;
            }
        });

        return collect($workpapers);
    }

    /**
     * Calcule l'avancement global de la révision pour une période.
     */
    public function getProgressStats(Client $client, FiscalYear $fiscalYear, string $period): array
    {
        $total = Workpaper::where('client_id', $client->id)
            ->where('fiscal_year_id', $fiscalYear->id)
            ->where('period', $period)
            ->count();

        $reviewed = Workpaper::where('client_id', $client->id)
            ->where('fiscal_year_id', $fiscalYear->id)
            ->where('period', $period)
            ->where('status', 'reviewed')
            ->count();

        $errors = Workpaper::where('client_id', $client->id)
            ->where('fiscal_year_id', $fiscalYear->id)
            ->where('period', $period)
            ->where('status', 'error')
            ->count();

        return [
            'total' => $total,
            'reviewed' => $reviewed,
            'pending' => $total - $reviewed - $errors,
            'error' => $errors,
            'percentage' => $total > 0 ? round(($reviewed / $total) * 100) : 0,
        ];
    }

    /**
     * Mettre à jour le statut d'un compte dans le dossier de révision.
     */
    public function updateStatus(int $workpaperId, string $status, int $reviewerId, ?string $notes = null)
    {
        $workpaper = Workpaper::findOrFail($workpaperId);
        $workpaper->status = $status;
        $workpaper->reviewer_id = $reviewerId;
        $workpaper->reviewed_at = now();
        
        if ($notes) {
            $workpaper->notes = $notes;
        }

        $workpaper->save();

        return $workpaper;
    }

    // --- Méthodes privées utilitaires ---

    private function calculateAccountBalance(AccountingAccount $account, FiscalYear $fiscalYear): float
    {
        // En conditions réelles, cela requêterait la table JournalEntry
        // Pour la démonstration, on génère un montant fictif basé sur l'ID du compte
        return 10000.00 + ($account->id * 1500);
    }

    private function calculatePreviousYearBalance(AccountingAccount $account, FiscalYear $fiscalYear): float
    {
        // Montant fictif N-1
        return 8000.00 + ($account->id * 1200);
    }

    private function detectAnomalousVariance(float $balanceN, float $balanceNMinus1): ?string
    {
        if ($balanceNMinus1 == 0) {
            return $balanceN > 50000 ? 'Nouveau compte avec un solde très élevé.' : null;
        }

        $variance = ($balanceN - $balanceNMinus1) / $balanceNMinus1;

        if ($variance > 1.5) { // +150%
            return 'Hausse inhabituelle du solde (>150%) par rapport à N-1. À vérifier.';
        } elseif ($variance < -0.8) { // -80%
            return 'Chute inhabituelle du solde (>80%) par rapport à N-1. À vérifier.';
        }

        return null;
    }
}
