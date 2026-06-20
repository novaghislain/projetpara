<?php

namespace App\Services\Accounting;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BalanceCalculationService
{
    /**
     * Balance générale pour un client sur un exercice donné.
     */
    public function balanceGenerale(
        int $clientId,
        ?int $fiscalYearId = null,
        ?string $classFilter = null,
        ?string $typeFilter = null,
        ?\DateTime $dateDebut = null,
        ?\DateTime $dateFin = null
    ): Collection {
        $query = AccountingJournalLine::select([
                'accounting_accounts.id as account_id',
                'accounting_accounts.code',
                'accounting_accounts.name',
                'accounting_accounts.type',
                'accounting_accounts.syscohada_class',
                DB::raw('SUM(COALESCE(accounting_journal_lines.debit, 0)) as total_debit'),
                DB::raw('SUM(COALESCE(accounting_journal_lines.credit, 0)) as total_credit'),
            ])
            ->join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->join('accounting_accounts', 'accounting_journal_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.status', 'posted')
            ->groupBy('accounting_accounts.id', 'accounting_accounts.code', 'accounting_accounts.name', 'accounting_accounts.type', 'accounting_accounts.syscohada_class')
            ->orderBy('accounting_accounts.code');

        if ($fiscalYearId) {
            $query->where('accounting_journals.fiscal_year_id', $fiscalYearId);
        }
        if ($classFilter) {
            $query->where('accounting_accounts.syscohada_class', $classFilter);
        }
        if ($typeFilter) {
            $query->where('accounting_accounts.type', $typeFilter);
        }
        if ($dateDebut) {
            $query->where('accounting_journals.entry_date', '>=', $dateDebut);
        }
        if ($dateFin) {
            $query->where('accounting_journals.entry_date', '<=', $dateFin);
        }

        $results = $query->get();

        // Calculer les soldes
        return $results->map(function ($item) {
            $item->solde = round($item->total_debit - $item->total_credit, 2);
            $item->solde_label = $item->solde >= 0 ? 'Débiteur' : 'Créditeur';
            return $item;
        });
    }

    /**
     * Balance auxiliaire pour un compte spécifique (ex: 411 - Clients).
     */
    public function balanceAuxiliaire(int $clientId, string $accountCode, int $fiscalYearId): Collection
    {
        $account = AccountingAccount::where('client_id', $clientId)
            ->where('code', $accountCode)
            ->firstOrFail();

        return AccountingJournalLine::select([
                'accounting_journals.id as journal_id',
                'accounting_journals.reference',
                'accounting_journals.entry_date',
                'accounting_journals.numero_piece',
                'accounting_journal_lines.label',
                DB::raw('COALESCE(accounting_journal_lines.debit, 0) as debit'),
                DB::raw('COALESCE(accounting_journal_lines.credit, 0) as credit'),
            ])
            ->join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.fiscal_year_id', $fiscalYearId)
            ->where('accounting_journals.status', 'posted')
            ->where('accounting_journal_lines.account_id', $account->id)
            ->orderBy('accounting_journals.entry_date')
            ->orderBy('accounting_journals.id')
            ->get();
    }

    /**
     * Soldes des comptes à une date donnée (pour bilan).
     */
    public function soldesAu(?\DateTime $date, int $clientId, ?int $fiscalYearId = null): Collection
    {
        $query = AccountingJournalLine::select([
                'accounting_accounts.id',
                'accounting_accounts.code',
                'accounting_accounts.name',
                'accounting_accounts.type',
                'accounting_accounts.syscohada_class',
                DB::raw('SUM(COALESCE(accounting_journal_lines.debit, 0)) as total_debit'),
                DB::raw('SUM(COALESCE(accounting_journal_lines.credit, 0)) as total_credit'),
            ])
            ->join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->join('accounting_accounts', 'accounting_journal_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.status', 'posted')
            ->groupBy('accounting_accounts.id', 'accounting_accounts.code', 'accounting_accounts.name', 'accounting_accounts.type', 'accounting_accounts.syscohada_class')
            ->orderBy('accounting_accounts.code');

        if ($fiscalYearId) {
            $query->where('accounting_journals.fiscal_year_id', $fiscalYearId);
        }
        if ($date) {
            $query->where('accounting_journals.entry_date', '<=', $date);
        }

        return $query->get()->map(function ($item) {
            if (in_array($item->type, ['asset', 'expense'])) {
                $item->solde = round($item->total_debit - $item->total_credit, 2);
            } else {
                $item->solde = round($item->total_credit - $item->total_debit, 2);
            }
            $item->solde = max(0, $item->solde); // Pas de solde négatif aux états financiers
            return $item;
        });
    }

    /**
     * Vérifier si les écritures sont équilibrées pour une période.
     */
    public function verifierEquilibre(int $clientId, ?int $fiscalYearId = null): array
    {
        $query = AccountingJournalLine::select([
                DB::raw('SUM(COALESCE(debit, 0)) as total_debit'),
                DB::raw('SUM(COALESCE(credit, 0)) as total_credit'),
            ])
            ->join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.status', 'posted');

        if ($fiscalYearId) {
            $query->where('accounting_journals.fiscal_year_id', $fiscalYearId);
        }

        $result = $query->first();

        return [
            'total_debit' => round($result->total_debit ?? 0, 2),
            'total_credit' => round($result->total_credit ?? 0, 2),
            'difference' => round(($result->total_debit ?? 0) - ($result->total_credit ?? 0), 2),
            'equilibre' => abs(($result->total_debit ?? 0) - ($result->total_credit ?? 0)) < 0.01,
        ];
    }
}
