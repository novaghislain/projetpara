<?php

namespace App\Services\Banking;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Journal;
use App\Models\AccountingAccount;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankTransactionService
{
    private JournalEntryService $entryService;

    public function __construct(JournalEntryService $entryService)
    {
        $this->entryService = $entryService;
    }

    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Crée une transaction bancaire et génère l'écriture comptable
     */
    public function createTransaction(array $data): BankTransaction
    {
        return DB::transaction(function () use ($data) {
            $clientId = $this->getClientId();

            $bankAccount = BankAccount::where('id', $data['bank_account_id'])
                ->where('client_id', $clientId)
                ->firstOrFail();

            // Calculer le solde cumulé
            $lastTransaction = BankTransaction::where('bank_account_id', $bankAccount->id)
                ->latest('transaction_date')
                ->latest('id')
                ->first();

            $lastBalance = $lastTransaction?->balance ?? $bankAccount->opening_balance;
            $debit = (float) ($data['debit'] ?? 0);
            $credit = (float) ($data['credit'] ?? 0);
            $newBalance = $lastBalance + $debit - $credit;

            // Catégorie auto
            $category = $data['category'] ?? ($debit > 0 ? 'income' : ($credit > 0 ? 'expense' : 'transfer'));

            $transaction = BankTransaction::create([
                'client_id' => $clientId,
                'bank_account_id' => $bankAccount->id,
                'transaction_date' => $data['transaction_date'],
                'value_date' => $data['value_date'] ?? $data['transaction_date'],
                'description' => $data['description'],
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'reference' => $data['reference'] ?? null,
                'cheque_number' => $data['cheque_number'] ?? null,
                'category' => $category,
                'status' => 'cleared',
                'is_reconciled' => false,
                'is_imported' => $data['is_imported'] ?? false,
                'notes' => $data['notes'] ?? null,
            ]);

            // Générer l'écriture comptable si demandé
            if (!empty($data['generate_entry'])) {
                $this->generateJournalEntry($transaction, $bankAccount, $data);
            }

            // Mettre à jour le solde du compte
            $bankAccount->increment('current_balance', $debit - $credit);

            return $transaction->fresh(['bankAccount']);
        });
    }

    /**
     * Importe plusieurs transactions (relevé bancaire)
     */
    public function importTransactions(int $bankAccountId, array $transactions): array
    {
        $clientId = $this->getClientId();
        $imported = [];

        foreach ($transactions as $row) {
            $txn = BankTransaction::firstOrCreate(
                [
                    'bank_account_id' => $bankAccountId,
                    'reference' => $row['reference'] ?? null,
                    'debit' => (float) ($row['debit'] ?? 0),
                    'credit' => (float) ($row['credit'] ?? 0),
                ],
                [
                    'client_id' => $clientId,
                    'transaction_date' => $row['transaction_date'],
                    'value_date' => $row['value_date'] ?? $row['transaction_date'],
                    'description' => $row['description'],
                    'balance' => $row['balance'] ?? 0,
                    'category' => $row['category'] ?? null,
                    'status' => 'pending',
                    'is_imported' => true,
                    'notes' => $row['notes'] ?? null,
                ]
            );
            if ($txn->wasRecentlyCreated) {
                $imported[] = $txn;
            }
        }

        return $imported;
    }

    /**
     * Rapproche une transaction (marque comme rapprochée)
     */
    public function markReconciled(int $transactionId): BankTransaction
    {
        $transaction = BankTransaction::where('id', $transactionId)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        $transaction->update([
            'is_reconciled' => true,
            'status' => 'reconciled',
        ]);

        return $transaction->fresh();
    }

    /**
     * Génère l'écriture comptable pour une transaction bancaire
     */
    private function generateJournalEntry(BankTransaction $transaction, BankAccount $bankAccount, array $data): void
    {
        $clientId = $this->getClientId();

        $journal = Journal::where('client_id', $clientId)
            ->where('code', 'BQ')
            ->firstOrFail();

        // Compte de contrepartie — chercher dans client puis global
        $counterpartAccountId = $data['counterpart_account_id']
            ?? AccountingAccount::where('client_id', $clientId)
                ->where('code', '531')
                ->value('id')
            ?? AccountingAccount::where('client_id', 0)
                ->where('code', '531')
                ->value('id');

        if (!$counterpartAccountId) {
            // Fallback: créer un compte caisse pour le client
            $global531 = AccountingAccount::where('client_id', 0)->where('code', '531')->first();
            if ($global531) {
                $newAccount = AccountingAccount::create([
                    'client_id' => $clientId,
                    'code' => '531',
                    'name' => 'Caisse principale',
                    'type' => 'asset',
                ]);
                $counterpartAccountId = $newAccount->id;
            }
        }

        $lines = [];

        // Ligne banque
        $lines[] = [
            'account_id' => $bankAccount->accounting_account_id,
            'description' => $transaction->description,
            'debit' => $transaction->debit,
            'credit' => $transaction->credit,
        ];

        // Ligne contrepartie (inversée)
        $lines[] = [
            'account_id' => $counterpartAccountId,
            'description' => $transaction->description,
            'debit' => $transaction->credit,
            'credit' => $transaction->debit,
        ];

        $entryData = [
            'journal_id' => $journal->id,
            'entry_date' => $transaction->transaction_date->format('Y-m-d'),
            'value_date' => $transaction->transaction_date->format('Y-m-d'),
            'reference' => $transaction->reference ?? 'BQ-' . $transaction->id,
            'description' => $transaction->description,
            'lines' => $lines,
        ];

        $entry = $this->entryService->createEntry($entryData);
        $this->entryService->postEntry($entry->id);

        $transaction->journal_entry_id = $entry->id;
        $transaction->save();
    }
}
