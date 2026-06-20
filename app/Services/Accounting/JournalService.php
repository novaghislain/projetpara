<?php

namespace App\Services\Accounting;

use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use App\Models\AccountingJournalSequence;
use App\Models\FiscalYear;
use App\Services\AuditTrailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JournalService
{
    /**
     * Créer une écriture comptable avec ses lignes.
     */
    public function creerEcriture(
        int $clientId,
        string $journalType,
        string $entryDate,
        array $lines,
        ?string $description = null,
        ?string $reference = null,
        ?int $fiscalYearId = null,
        ?string $sourceModule = null,
        ?array $sourceable = null,
    ): AccountingJournal {
        if (!$fiscalYearId) {
            $fiscalYear = FiscalYear::where('client_id', $clientId)
                ->where('status', 'open')
                ->firstOrFail();
            $fiscalYearId = $fiscalYear->id;
        }

        // Vérifier que l'exercice est ouvert
        $exercice = FiscalYear::findOrFail($fiscalYearId);
        if (!$exercice->canEdit()) {
            throw new \RuntimeException("L'exercice comptable {$exercice->year} est clos ou verrouillé.");
        }

        // Valider que les lignes sont équilibrées
        $totalDebit = collect($lines)->sum('debit');
        $totalCredit = collect($lines)->sum('credit');
        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new \InvalidArgumentException(
                "Écriture déséquilibrée : débit ($totalDebit) ≠ crédit ($totalCredit)"
            );
        }

        // Générer le numéro de pièce
        $numeroPiece = AccountingJournalSequence::getNextNumber(
            $clientId, $journalType, $fiscalYearId
        );

        return DB::transaction(function () use ($clientId, $journalType, $entryDate, $lines, $description, $reference, $fiscalYearId, $sourceModule, $sourceable, $numeroPiece) {
            $journal = AccountingJournal::create([
                'client_id' => $clientId,
                'journal_type' => $journalType,
                'entry_date' => $entryDate,
                'reference' => $reference ?? $numeroPiece,
                'description' => $description,
                'status' => 'draft',
                'created_by' => Auth::id(),
                'fiscal_year_id' => $fiscalYearId,
                'numero_piece' => $numeroPiece,
                'source_module' => $sourceModule,
            ]);

            foreach ($lines as $line) {
                AccountingJournalLine::create([
                    'journal_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'label' => $line['label'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'reference_document' => $line['reference_document'] ?? null,
                    'reference_type' => $line['reference_type'] ?? null,
                    'due_date' => $line['due_date'] ?? null,
                    'cost_center_id' => $line['cost_center_id'] ?? null,
                ]);
            }

            AuditTrailService::log($journal, 'created', [], $journal->toArray(),
                'Écriture créée : ' . $journal->numero_piece, $clientId, Auth::id());

            return $journal->load('lines');
        });
    }

    /**
     * Valider (poster) une écriture comptable.
     */
    public function validerEcriture(int $journalId, ?int $userId = null): AccountingJournal
    {
        $journal = AccountingJournal::with('lines')->findOrFail($journalId);

        if ($journal->status === 'posted') {
            throw new \RuntimeException('Cette écriture est déjà validée.');
        }

        if (!$journal->fiscalYear || !$journal->fiscalYear->canEdit()) {
            throw new \RuntimeException('L\'exercice comptable est clos.');
        }

        return DB::transaction(function () use ($journal, $userId) {
            $journal->update([
                'status' => 'posted',
                'validated_by' => $userId ?? Auth::id(),
                'validated_at' => now(),
            ]);

            AuditTrailService::log($journal, 'validated',
                ['status' => 'draft'], ['status' => 'posted'],
                'Écriture validée : ' . $journal->numero_piece,
                $journal->client_id, $userId);

            return $journal->fresh('lines');
        });
    }

    /**
     * Extourne (annulation) d'une écriture.
     * Crée une nouvelle écriture avec les montants inversés.
     */
    public function extournerEcriture(int $originalJournalId, ?string $description = null, ?int $userId = null): AccountingJournal
    {
        $original = AccountingJournal::with('lines')->findOrFail($originalJournalId);

        if ($original->is_reversal) {
            throw new \RuntimeException('Impossible d\'extourner une extourne.');
        }

        return DB::transaction(function () use ($original, $description, $userId) {
            $lines = $original->lines->map(function ($line) {
                return [
                    'account_id' => $line->account_id,
                    'label' => 'Extourne : ' . ($line->label ?: 'Sans libellé'),
                    'debit' => $line->credit,  // Inverser
                    'credit' => $line->debit,   // Inverser
                ];
            })->toArray();

            $extourne = $this->creerEcriture(
                clientId: $original->client_id,
                journalType: $original->journal_type,
                entryDate: now()->format('Y-m-d'),
                lines: $lines,
                description: $description ?? "Extourne de l'écriture {$original->numero_piece}",
                fiscalYearId: $original->fiscal_year_id,
                sourceModule: 'extourne',
            );

            // Marquer l'extourne
            $extourne->update([
                'is_reversal' => true,
                'reversed_journal_id' => $original->id,
            ]);

            // Valider automatiquement
            $this->validerEcriture($extourne->id, $userId);

            AuditTrailService::log($original, 'reversed',
                [], ['reversed_by' => $extourne->id],
                'Écriture extournée : ' . $original->numero_piece,
                $original->client_id, $userId);

            return $extourne->load('lines');
        });
    }

    /**
     * Supprimer une écriture (seulement si brouillon).
     */
    public function supprimerEcriture(int $journalId): bool
    {
        $journal = AccountingJournal::findOrFail($journalId);

        if ($journal->status !== 'draft') {
            throw new \RuntimeException('Seules les écritures en brouillon peuvent être supprimées.');
        }

        return DB::transaction(function () use ($journal) {
            $journal->lines()->delete();
            $journal->delete();
            return true;
        });
    }

    /**
     * Obtenir le solde d'un compte sur une période.
     */
    public function soldeCompte(int $clientId, int $accountId, ?int $fiscalYearId = null): float
    {
        $query = AccountingJournalLine::where('account_id', $accountId)
            ->join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.status', 'posted');

        if ($fiscalYearId) {
            $query->where('accounting_journals.fiscal_year_id', $fiscalYearId);
        }

        $result = $query->select([
            \DB::raw('SUM(COALESCE(accounting_journal_lines.debit, 0)) as total_debit'),
            \DB::raw('SUM(COALESCE(accounting_journal_lines.credit, 0)) as total_credit'),
        ])->first();

        return round(($result->total_debit ?? 0) - ($result->total_credit ?? 0), 2);
    }
}
