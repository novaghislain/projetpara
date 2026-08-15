<?php

namespace App\Services\Banking;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\BankTransaction;
use App\Services\IA\BankReconciliationAiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankReconciliationService
{
    protected BankReconciliationAiService $aiService;

    public function __construct(BankReconciliationAiService $aiService)
    {
        $this->aiService = $aiService;
    }
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Crée un nouveau rapprochement bancaire
     */
    public function createReconciliation(array $data): BankReconciliation
    {
        return DB::transaction(function () use ($data) {
            $clientId = $this->getClientId();

            $bankAccount = BankAccount::where('id', $data['bank_account_id'])
                ->where('client_id', $clientId)
                ->firstOrFail();

            // Générer la référence
            $count = BankReconciliation::where('client_id', $clientId)
                ->where('bank_account_id', $bankAccount->id)
                ->count() + 1;
            $reference = 'RAPRO-' . $bankAccount->account_number . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);

            // Calculer le solde d'ouverture = solde de fin du dernier rapprochement
            $lastRecon = BankReconciliation::where('bank_account_id', $bankAccount->id)
                ->where('status', BankReconciliation::STATUS_COMPLETED)
                ->latest('end_date')
                ->first();

            $openingBalance = $lastRecon?->closing_balance ?? $bankAccount->opening_balance;

            // Transactions sur la période
            $transactions = BankTransaction::where('bank_account_id', $bankAccount->id)
                ->whereBetween('transaction_date', [$data['start_date'], $data['end_date']])
                ->where('status', '!=', 'pending')
                ->get();

            $totalDebit = $transactions->sum('debit');
            $totalCredit = $transactions->sum('credit');

            // Solde de clôture calculé
            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            $reconciliation = BankReconciliation::create([
                'client_id' => $clientId,
                'bank_account_id' => $bankAccount->id,
                'reference' => $reference,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'statement_balance' => $data['statement_balance'],
                'difference' => $closingBalance - (float) $data['statement_balance'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'adjusted_balance' => $data['statement_balance'],
                'status' => BankReconciliation::STATUS_DRAFT,
                'created_by' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);

            return $reconciliation->fresh(['bankAccount']);
        });
    }

    /**
     * Ajoute un élément de rapprochement (match manuel)
     */
    public function addReconciliationItem(int $reconciliationId, int $transactionId, string $type): BankReconciliationItem
    {
        return DB::transaction(function () use ($reconciliationId, $transactionId, $type) {
            $clientId = $this->getClientId();

            $reconciliation = BankReconciliation::where('id', $reconciliationId)
                ->where('client_id', $clientId)
                ->firstOrFail();

            if ($reconciliation->status !== BankReconciliation::STATUS_DRAFT
                && $reconciliation->status !== BankReconciliation::STATUS_IN_PROGRESS) {
                throw ValidationException::withMessages([
                    'status' => 'Le rapprochement n\'est pas modifiable.',
                ]);
            }

            $transaction = BankTransaction::where('id', $transactionId)
                ->where('client_id', $clientId)
                ->firstOrFail();

            $amount = max($transaction->debit, $transaction->credit);

            $item = BankReconciliationItem::firstOrCreate(
                [
                    'reconciliation_id' => $reconciliation->id,
                    'transaction_id' => $transaction->id,
                ],
                [
                    'client_id' => $clientId,
                    'type' => $type,
                    'status' => 'cleared',
                    'amount' => $amount,
                ]
            );

            // Marquer la transaction comme rapprochée
            $transaction->update([
                'is_reconciled' => true,
                'status' => 'reconciled',
            ]);

            return $item->fresh(['transaction']);
        });
    }

    /**
     * Finalise le rapprochement
     */
    public function completeReconciliation(int $reconciliationId): BankReconciliation
    {
        return DB::transaction(function () use ($reconciliationId) {
            $clientId = $this->getClientId();

            $reconciliation = BankReconciliation::where('id', $reconciliationId)
                ->where('client_id', $clientId)
                ->firstOrFail();

            if ($reconciliation->status !== BankReconciliation::STATUS_DRAFT
                && $reconciliation->status !== BankReconciliation::STATUS_IN_PROGRESS) {
                throw ValidationException::withMessages([
                    'status' => 'Le rapprochement n\'est pas modifiable.',
                ]);
            }

            // Vérifier la différence
            $difference = $reconciliation->closing_balance - $reconciliation->statement_balance;
            if (abs($difference) > 0.01) {
                throw ValidationException::withMessages([
                    'difference' => "Le solde ne correspond pas. Différence: {$difference} FCFA. Ajustez les éléments de rapprochement.",
                ]);
            }

            $reconciliation->update([
                'status' => BankReconciliation::STATUS_COMPLETED,
                'difference' => 0,
                'adjusted_balance' => $reconciliation->statement_balance,
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            // Mettre à jour le solde rapproché du compte bancaire
            $reconciliation->bankAccount->update([
                'reconciled_balance' => $reconciliation->statement_balance,
                'last_reconciliation_date' => $reconciliation->end_date,
            ]);

            return $reconciliation->fresh(['bankAccount', 'items.transaction']);
        });
    }

    /**
     * Suggestion automatique de pointage (match par montant)
     */
    public function autoSuggest(int $reconciliationId): array
    {
        $reconciliation = BankReconciliation::where('id', $reconciliationId)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        // Transactions non rapprochées sur la période
        $pendingTransactions = BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->where('is_reconciled', false)
            ->get();

        $suggestions = [];
        $matchedIds = [];

        // Stratégie 1 : Transactions miroir exactes
        foreach ($pendingTransactions as $txn) {
            $amount = max($txn->debit, $txn->credit);
            if ($amount <= 0 || in_array($txn->id, $matchedIds)) continue;

            $mirror = $pendingTransactions->first(function ($t) use ($amount, $matchedIds, $txn) {
                $tAmount = max($t->debit, $t->credit);
                return $t->id !== $txn->id
                    && !in_array($t->id, $matchedIds)
                    && abs($tAmount - $amount) < 0.01;
            });

            if ($mirror) {
                $suggestions[] = [
                    'type' => 'mirror',
                    'debit_transaction_id' => $txn->debit > 0 ? $txn->id : $mirror->id,
                    'credit_transaction_id' => $txn->credit > 0 ? $txn->id : $mirror->id,
                    'amount' => $amount,
                    'confidence_score' => 100,
                    'reason' => 'Transactions miroir exactes trouvées dans le relevé.'
                ];
                $matchedIds[] = $txn->id;
                $matchedIds[] = $mirror->id;
            }
        }

        // Stratégie 2 : Intelligence Artificielle (Fuzzy Matching avec factures ouvertes)
        foreach ($pendingTransactions as $txn) {
            if (in_array($txn->id, $matchedIds)) continue;
            
            $aiMatches = $this->aiService->suggestMatchesForTransaction($txn);
            if (!empty($aiMatches)) {
                $bestMatch = $aiMatches[0];
                $suggestions[] = [
                    'type' => 'invoice',
                    'transaction_id' => $txn->id,
                    'invoice_id' => $bestMatch['invoice_id'],
                    'invoice_number' => $bestMatch['invoice_number'],
                    'amount' => $bestMatch['invoice_amount'],
                    'confidence_score' => $bestMatch['confidence_score'],
                    'reason' => $bestMatch['reason']
                ];
                $matchedIds[] = $txn->id;
            }
        }

        return $suggestions;
    }
}
