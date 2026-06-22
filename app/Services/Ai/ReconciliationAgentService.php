<?php

namespace App\Services\Ai;

use App\Models\AiSuggestion;
use App\Models\AiLearningLog;
use App\Models\BankReconciliation;
use App\Models\AccountingAccount;
use App\Services\Accounting\BankReconciliationService;

class ReconciliationAgentService
{
    public function __construct(
        private BankReconciliationService $reconciliationService
    ) {}

    /**
     * Analyser les écarts de rapprochement et suggérer des actions.
     */
    public function analyze(int $clientId): array
    {
        $suggestions = [];

        // Trouver les rapprochements en cours
        $pendingReconciliations = BankReconciliation::where('client_id', $clientId)
            ->whereIn('status', ['draft', 'in_progress'])
            ->get();

        foreach ($pendingReconciliations as $rec) {
            $diff = abs($rec->difference);

            if ($diff > 1000) {
                $suggestions[] = [
                    'reconciliation_id' => $rec->id,
                    'period' => $rec->period,
                    'bank_account' => $rec->bank_account,
                    'difference' => $rec->difference,
                    'balance_per_statement' => $rec->balance_per_statement,
                    'balance_per_books' => $rec->balance_per_books,
                ];
            }
        }

        if (count($suggestions)) {
            $suggestion = AiSuggestion::create([
                'client_id' => $clientId,
                'agent' => 'reconciliation',
                'type' => 'reconciliation_alert',
                'title' => count($suggestions) . ' rapprochement(s) avec écart significatif',
                'description' => 'Des écarts de plus de 1 000 FCFA nécessitent votre attention.',
                'data' => ['pending' => $suggestions],
                'metadata' => ['agent' => 'Rapprochement', 'version' => '1.0'],
                'status' => 'pending',
            ]);

            return ['suggestion_id' => $suggestion->id, 'count' => count($suggestions)];
        }

        return ['suggestion_id' => null, 'count' => 0];
    }

    /**
     * Suggérer le lettrage automatique des comptes.
     */
    public function suggestMatching(int $clientId): array
    {
        $comptesTiers = AccountingAccount::where('client_id', $clientId)
            ->whereIn('code', ['401', '411'])
            ->get();

        $total = 0;
        foreach ($comptesTiers as $compte) {
            $total += $compte->entries()->whereNull('lettrage_code')->count();
        }

        if ($total > 0) {
            $suggestion = AiSuggestion::create([
                'client_id' => $clientId,
                'agent' => 'reconciliation',
                'type' => 'lettrage',
                'title' => 'Lettrage automatique disponible',
                'description' => $total . ' écriture(s) non lettrées sur les comptes 401 (fournisseurs) et 411 (clients).',
                'data' => ['unmatched_count' => $total],
                'metadata' => ['agent' => 'Rapprochement', 'version' => '1.0'],
                'status' => 'pending',
            ]);
            return ['suggestion_id' => $suggestion->id, 'count' => $total];
        }

        return ['suggestion_id' => null, 'count' => 0];
    }

    public function summary(int $clientId): array
    {
        $pending = AiSuggestion::byClient($clientId)->byAgent('reconciliation')->pending()->count();
        $openRecs = BankReconciliation::where('client_id', $clientId)->whereIn('status', ['draft', 'in_progress'])->count();

        return [
            'pending_suggestions' => $pending,
            'open_reconciliations' => $openRecs,
            'status' => $openRecs > 0 ? 'attention' : 'ok',
        ];
    }
}
