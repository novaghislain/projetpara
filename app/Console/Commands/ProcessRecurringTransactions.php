<?php

namespace App\Console\Commands;

use App\Models\AccountingAccount;
use App\Models\EntryLine;
use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Notification;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProcessRecurringTransactions — Automatisation CDC §13.4.
 *
 * Exécute les transactions récurrentes arrivées à échéance :
 *   - type 'scheduled'  → écriture de journal comptable équilibrée
 *     (créée en mode brouillon, résolution journal + période fiscale ouverte,
 *     sans session utilisateur — sûr en CLI) ;
 *   - type 'reminder'   → notification au créateur du modèle ;
 *   - type 'template'   → ponctuel à la demande, jamais auto-exécuté.
 *
 * Après traitement, la prochaine occurrence est avancée selon la fréquence ;
 * le modèle est désactivé si la date de fin ou le nb max d'occurrences est atteint.
 */
class ProcessRecurringTransactions extends Command
{
    protected $signature = 'recurring:process {--client-id=}';
    protected $description = 'ProcessRecurringTransactions — exécute les transactions récurrentes échues (CDC 13.4)';

    /** Décalages par fréquence pour avancer la prochaine occurrence. */
    protected const FREQUENCY_STEPS = [
        'daily'     => ['addDays', 1],
        'weekly'    => ['addWeeks', 1],
        'biweekly'  => ['addWeeks', 2],
        'monthly'   => ['addMonths', 1],
        'quarterly' => ['addMonths', 3],
        'yearly'    => ['addYears', 1],
    ];

    public function handle(): int
    {
        $this->info('🔄 Transactions récurrentes — traitement des échéances...');

        $query = RecurringTransaction::query()
            ->where('is_active', true)
            ->whereNotNull('next_occurrence')
            ->where('next_occurrence', '<=', Carbon::today())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', Carbon::today());
            })
            ->where(function ($q) {
                $q->whereNull('max_occurrences')->orWhereColumn('occurrences_count', '<', 'max_occurrences');
            });

        if ($this->option('client-id')) {
            $query->where('client_id', $this->option('client-id'));
        }

        $recurrings = $query->orderBy('next_occurrence')->get();

        if ($recurrings->isEmpty()) {
            $this->info('Aucune transaction récurrente échue.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($recurrings->count());
        $bar->start();

        $processed = 0;
        $skipped = 0;

        foreach ($recurrings as $recurring) {
            $ok = false;

            if ($recurring->type === 'scheduled') {
                $ok = $this->processScheduled($recurring);
            } elseif ($recurring->type === 'reminder') {
                $ok = $this->processReminder($recurring);
            }

            if ($ok) {
                $this->advance($recurring);
                $processed++;
            } else {
                $skipped++;
                // On laisse next_occurrence inchangée → nouvelle tentative au prochain run.
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Transactions récurrentes : {$processed} traitées, {$skipped} ignorées.");

        return Command::SUCCESS;
    }

    /**
     * Exécute une transaction 'scheduled' : écriture de journal équilibrée
     * à partir de template_data (montant + compte débit + compte crédit).
     */
    protected function processScheduled(RecurringTransaction $recurring): bool
    {
        $data = $recurring->template_data ?? [];
        $amount = (float) ($data['amount'] ?? 0);
        $accountDebit = (string) ($data['account_debit'] ?? '');
        $accountCredit = (string) ($data['account_credit'] ?? '');
        $entryDate = Carbon::today();

        if ($amount <= 0 || $accountDebit === '' || $accountCredit === '') {
            Log::warning('recurring:process — données incomplètes (montant ou comptes manquants)', [
                'recurring_id' => $recurring->id,
            ]);
            return false;
        }

        try {
            DB::beginTransaction();

            // Résolution du journal : Opérations diverses → défaut → premier actif.
            // Les journaux globaux SYSCOHADA (client_id NULL) sont réutilisables
            // par n'importe quel client.
            $journalBase = Journal::where('is_active', true)
                ->where(function ($q) use ($recurring) {
                    $q->where('client_id', $recurring->client_id)->orWhereNull('client_id');
                });

            $journal = (clone $journalBase)->where('type', 'operations_diverses')->first()
                ?? (clone $journalBase)->where('is_default', true)->first()
                ?? $journalBase->first();

            if (!$journal) {
                Log::warning('recurring:process — aucun journal actif pour le client', [
                    'client_id' => $recurring->client_id,
                ]);
                return false;
            }

            // Exercice fiscal : ouvert, couvrant l'année de l'écriture.
            // Auto-créé s'il manque (même auto-réparation que FiscalBeninService).
            $fiscalYear = FiscalYear::firstOrCreate(
                ['client_id' => $recurring->client_id, 'year' => $entryDate->year],
                [
                    'date_start' => $entryDate->startOfYear()->toDateString(),
                    'date_end'   => $entryDate->endOfYear()->toDateString(),
                    'status'     => 'open',
                ]
            );

            // Période fiscale mensuelle ouverte couvrant la date d'écriture.
            $period = FiscalPeriod::where('fiscal_year_id', $fiscalYear->id)
                ->whereDate('start_date', '<=', $entryDate)
                ->whereDate('end_date', '>=', $entryDate)
                ->where('status', 'open')
                ->first();

            if (!$period) {
                $period = FiscalPeriod::create([
                    'fiscal_year_id' => $fiscalYear->id,
                    'code'           => 'M' . str_pad($entryDate->month, 2, '0', STR_PAD_LEFT),
                    'label'          => $entryDate->translatedFormat('F Y'),
                    'start_date'     => $entryDate->startOfMonth()->toDateString(),
                    'end_date'       => $entryDate->endOfMonth()->toDateString(),
                    'status'         => 'open',
                    'is_current'     => true,
                ]);
            }

            // Résolution des comptes (code exact puis préfixe SYSCOHADA 6 chiffres ;
            // comptes du client OU globaux client_id=0).
            $debitAccount = $this->resolveAccount($recurring->client_id, $accountDebit);
            $creditAccount = $this->resolveAccount($recurring->client_id, $accountCredit);

            if (!$debitAccount || !$creditAccount) {
                Log::warning('recurring:process — compte comptable introuvable', [
                    'recurring_id'   => $recurring->id,
                    'account_debit'  => $accountDebit,
                    'account_credit' => $accountCredit,
                ]);
                return false;
            }

            $debitLabel  = $debitAccount->name ?? $accountDebit;
            $creditLabel = $creditAccount->name ?? $accountCredit;

            $entry = JournalEntry::create([
                'client_id'        => $recurring->client_id,
                'journal_id'       => $journal->id,
                'fiscal_period_id' => $period->id,
                'entry_number'     => $journal->next_entry_number,
                'entry_date'       => $entryDate,
                'reference'        => 'REC-' . $recurring->id . '-' . $entryDate->format('Ymd'),
                'description'      => $data['description'] ?? $recurring->title,
                'total_debit'      => $amount,
                'total_credit'     => $amount,
                'is_balanced'      => true,
                'status'           => JournalEntry::STATUS_DRAFT,
                'created_by'       => $recurring->created_by,
            ]);

            EntryLine::create([
                'client_id'    => $recurring->client_id,
                'entry_id'     => $entry->id,
                'line_number'  => 1,
                'account_id'   => $debitAccount?->id,
                'account_code' => $accountDebit,
                'account_label'=> $debitLabel,
                'description'  => $entry->description,
                'debit'        => $amount,
                'credit'       => 0,
            ]);

            EntryLine::create([
                'client_id'    => $recurring->client_id,
                'entry_id'     => $entry->id,
                'line_number'  => 2,
                'account_id'   => $creditAccount?->id,
                'account_code' => $accountCredit,
                'account_label'=> $creditLabel,
                'description'  => $entry->description,
                'debit'        => 0,
                'credit'       => $amount,
            ]);

            $journal->incrementNextNumber();

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('recurring:process — erreur création écriture récurrente', [
                'recurring_id' => $recurring->id,
                'error'        => $e->getMessage(),
            ]);
            return false;
        }
    }

    /** Crée une notification de rappel au créateur du modèle. */
    protected function processReminder(RecurringTransaction $recurring): bool
    {
        if (!$recurring->created_by) {
            return false;
        }

        try {
            $message = $recurring->template_data['description'] ?? $recurring->title
                . ' — Échéance récurrente, action manuelle requise.';

            Notification::create([
                'user_id' => $recurring->created_by,
                'type'    => 'reminder',
                'title'   => 'Rappel : ' . $recurring->title,
                'message' => $message,
                'data'    => [
                    'client_id'    => $recurring->client_id,
                    'recurring_id' => $recurring->id,
                ],
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('recurring:process — erreur notification de rappel', [
                'recurring_id' => $recurring->id,
                'error'        => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Résout un compte comptable par code exact puis par préfixe
     * (codes courts type "613" → compte SYSCOHADA "613000"). Comptes du
     * client OU globaux (client_id = 0).
     */
    protected function resolveAccount(int $clientId, string $code): ?AccountingAccount
    {
        $base = function () use ($clientId, $code) {
            return AccountingAccount::where(function ($q) use ($clientId) {
                    $q->where('client_id', $clientId)->orWhere('client_id', 0);
                })
                ->where('is_active', true);
        };

        // Code exact
        $account = (clone $base())->where('code', $code)->first();
        if ($account) {
            return $account;
        }

        // Préfixe (le compte le plus court = le plus haut niveau)
        return (clone $base())
            ->where('code', 'like', $code . '%')
            ->orderByRaw('CHAR_LENGTH(code) ASC')
            ->first();
    }

    /** Avance la prochaine occurrence et désactive le modèle si épuisé. */
    protected function advance(RecurringTransaction $recurring): void
    {
        $next = clone $recurring->next_occurrence;

        [$method, $step] = self::FREQUENCY_STEPS[$recurring->frequency] ?? ['addMonths', 1];
        $next->{$method}($step);

        $update = [
            'last_occurrence'   => Carbon::today(),
            'next_occurrence'   => $next,
            'occurrences_count' => $recurring->occurrences_count + 1,
        ];

        // Désactivation si date de fin atteinte ou nb max d'occurrences dépassé.
        if (
            ($recurring->end_date && $next->greaterThan($recurring->end_date))
            || ($recurring->max_occurrences && $update['occurrences_count'] >= $recurring->max_occurrences)
        ) {
            $update['is_active'] = false;
        }

        $recurring->update($update);
    }
}
