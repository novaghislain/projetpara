<?php

namespace App\Services\Accounting;

use App\Models\BankReconciliation;
use App\Models\AccountingJournal;
use App\Models\AccountingAccount;
use App\Models\FiscalYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de rapprochement bancaire.
 *
 * Compare les écritures comptables avec les relevés bancaires
 * et aide à identifier les écarts.
 */
class BankReconciliationService
{
    /**
     * Initialiser un rapprochement avec les soldes.
     */
    public function initialize(int $clientId, int $fiscalYearId, string $bankAccount, array $statement): BankReconciliation
    {
        $balanceBooks = $this->getBookBalance($clientId, $bankAccount);

        $reconciliation = BankReconciliation::create([
            'client_id'             => $clientId,
            'fiscal_year_id'        => $fiscalYearId,
            'bank_account'          => $bankAccount,
            'bank_name'             => $statement['bank_name'] ?? '',
            'period'                => $statement['period'] ?? now()->format('Y-m'),
            'statement_date'        => $statement['statement_date'] ?? now(),
            'balance_per_statement' => $statement['balance'] ?? 0,
            'balance_per_books'     => $balanceBooks,
            'difference'            => ($statement['balance'] ?? 0) - $balanceBooks,
            'status'                => 'draft',
        ]);

        Log::info('Bank reconciliation initialized', [
            'reconciliation_id' => $reconciliation->id,
            'client_id' => $clientId,
        ]);

        return $reconciliation;
    }

    /**
     * Calculer les écarts automatiquement.
     */
    public function calculateDifferences(BankReconciliation $reconciliation): array
    {
        $journalEntries = $this->getJournalEntries(
            $reconciliation->client_id,
            $reconciliation->bank_account,
            $reconciliation->period
        );

        $outstandingDeposits = $journalEntries
            ->where('type', 'deposit')
            ->where('is_cleared', false)
            ->sum('amount');

        $outstandingChecks = $journalEntries
            ->where('type', 'withdrawal')
            ->where('is_cleared', false)
            ->sum('amount');

        $unmatched = $journalEntries
            ->where('is_cleared', false)
            ->values()
            ->toArray();

        $difference = $reconciliation->balance_per_statement
            - $reconciliation->balance_per_books
            - $outstandingDeposits
            + $outstandingChecks;

        $reconciliation->update([
            'outstanding_deposits' => $outstandingDeposits,
            'outstanding_checks'   => $outstandingChecks,
            'unmatched_items'      => $unmatched,
            'difference'           => $difference,
            'bank_charges'         => $this->calculateBankCharges($journalEntries),
            'interest_income'      => $this->calculateInterest($journalEntries),
        ]);

        return [
            'difference'           => $difference,
            'outstanding_deposits' => $outstandingDeposits,
            'outstanding_checks'   => $outstandingChecks,
            'unmatched_count'      => count($unmatched),
        ];
    }

    /**
     * Marquer une écriture comme rapprochée.
     */
    public function matchItem(BankReconciliation $reconciliation, int $journalLineId, string $action = 'match'): array
    {
        $unmatched = collect($reconciliation->unmatched_items ?? []);
        $updated = $unmatched->map(function ($item) use ($journalLineId, $action) {
            if (($item['id'] ?? null) === $journalLineId) {
                $item['status'] = $action === 'match' ? 'matched' : 'excluded';
            }
            return $item;
        });

        $reconciliation->update(['unmatched_items' => $updated->toArray()]);

        // Recalculer après match
        $this->calculateDifferences($reconciliation);

        Log::info("Bank reconciliation item $action ed", [
            'reconciliation_id' => $reconciliation->id,
            'line_id' => $journalLineId,
            'action'  => $action,
        ]);

        return ['success' => true, 'unmatched_remaining' => $updated->where('status', 'pending')->count()];
    }

    /**
     * Valider le rapprochement (solde = 0).
     */
    public function validateReconciliation(BankReconciliation $reconciliation): array
    {
        $this->calculateDifferences($reconciliation);

        if (abs((float) $reconciliation->difference) > 0.01) {
            return [
                'success' => false,
                'error'   => "Le solde restant est de {$reconciliation->difference} €. Corrigez les écarts avant de valider.",
            ];
        }

        $reconciliation->update(['status' => 'validated']);

        Log::info('Bank reconciliation validated', ['reconciliation_id' => $reconciliation->id]);

        return ['success' => true, 'message' => 'Rapprochement bancaire validé.'];
    }

    /**
     * Récupérer le solde comptable d'un compte bancaire.
     */
    private function getBookBalance(int $clientId, string $bankAccount): float
    {
        $account = AccountingAccount::where('client_id', $clientId)
            ->where('account_number', $bankAccount)
            ->first();

        return $account ? (float) ($account->balance ?? 0) : 0;
    }

    /**
     * Récupérer les écritures de la période pour le compte.
     */
    private function getJournalEntries(int $clientId, string $bankAccount, string $period): Collection
    {
        $journals = AccountingJournal::where('client_id', $clientId)
            ->where('journal_date', 'like', "$period%")
            ->with(['lines' => function ($q) use ($bankAccount) {
                $q->whereHas('account', fn ($a) => $a->where('account_number', $bankAccount));
            }])
            ->get();

        $entries = collect();
        foreach ($journals as $journal) {
            foreach ($journal->lines as $line) {
                $entries->push([
                    'id'          => $line->id,
                    'date'        => $journal->journal_date,
                    'description' => $line->description ?? $journal->description,
                    'type'        => ($line->debit > 0) ? 'deposit' : 'withdrawal',
                    'amount'      => (float) max($line->debit, $line->credit),
                    'is_cleared'  => $line->is_cleared ?? false,
                    'status'      => 'pending',
                ]);
            }
        }

        return $entries;
    }

    private function calculateBankCharges(Collection $entries): float
    {
        return (float) $entries->where('description', 'like', '%frais%')->sum('amount');
    }

    private function calculateInterest(Collection $entries): float
    {
        return (float) $entries->where('description', 'like', '%intérêt%')->sum('amount');
    }
}
