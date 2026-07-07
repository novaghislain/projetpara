<?php
namespace App\Services\Reports;

use App\Models\AccountingAccount;
use Illuminate\Support\Facades\DB;

class IncomeStatementService
{
    /**
     * Compte de Résultat (P&L) selon SYSCOHADA
     *
     * Charges  : classe 6
     * Produits : classe 7
     * Soldes intermédiaires de gestion (SIG)
     */
    public function generate(int $clientId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-01-01');
        $endDate = $endDate ?? date('Y-m-d');

        // === PRODUITS D'EXPLOITATION (70-76) ===
        $produitsExploitation = 0;
        foreach (['70', '71', '72', '73', '74', '75', '76'] as $prefix) {
            $produitsExploitation += $this->getClassBalance($clientId, $prefix, $startDate, $endDate, 'credit');
        }

        // === CHARGES D'EXPLOITATION (60-66) ===
        $chargesExploitation = 0;
        foreach (['60', '61', '62', '63', '64', '65', '66'] as $prefix) {
            $chargesExploitation += $this->getClassBalance($clientId, $prefix, $startDate, $endDate, 'debit');
        }

        $resultatExploitation = $produitsExploitation - $chargesExploitation;

        // === RÉSULTAT FINANCIER ===
        $produitsFinanciers = $this->getClassBalance($clientId, '77', $startDate, $endDate, 'credit');
        $chargesFinancieres = $this->getClassBalance($clientId, '67', $startDate, $endDate, 'debit');
        $resultatFinancier = $produitsFinanciers - $chargesFinancieres;

        $resultatAvantImpot = $resultatExploitation + $resultatFinancier;

        // === IMPÔTS (68) ===
        $impots = $this->getClassBalance($clientId, '68', $startDate, $endDate, 'debit');

        // === EXCEPTIONNEL (78) ===
        $produitsExceptionnels = $this->getClassBalance($clientId, '78', $startDate, $endDate, 'credit');
        $chargesExceptionnelles = $this->getClassBalance($clientId, '68', $startDate, $endDate, 'debit');

        $resultatNet = $resultatAvantImpot - $impots + $produitsExceptionnels - $chargesExceptionnelles;

        // Détail par comptes
        $chargesDetails = $this->getAccountDetails($clientId, '6', $startDate, $endDate, 'debit');
        $produitsDetails = $this->getAccountDetails($clientId, '7', $startDate, $endDate, 'credit');

        return [
            'parameters' => [
                'client_id' => $clientId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'fiscal_year' => date('Y', strtotime($startDate)),
            ],
            'resultat_exploitation' => [
                'produits' => round($produitsExploitation, 2),
                'charges' => round($chargesExploitation, 2),
                'resultat' => round($resultatExploitation, 2),
            ],
            'resultat_financier' => [
                'produits' => round($produitsFinanciers, 2),
                'charges' => round($chargesFinancieres, 2),
                'resultat' => round($resultatFinancier, 2),
            ],
            'resultat_avant_impot' => round($resultatAvantImpot, 2),
            'impots' => round($impots, 2),
            'resultat_exceptionnel' => [
                'produits' => round($produitsExceptionnels, 2),
                'charges' => round($chargesExceptionnelles, 2),
            ],
            'resultat_net' => round($resultatNet, 2),
            'details_charges' => $chargesDetails,
            'details_produits' => $produitsDetails,
        ];
    }

    /**
     * Soldes Intermédiaires de Gestion (SIG) détaillés
     */
    public function generateSIG(int $clientId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-01-01');
        $endDate = $endDate ?? date('Y-m-d');

        // Marge commerciale = Ventes de marchandises (707) - Achats de marchandises (607)
        $ventesMarchandises = $this->getClassBalance($clientId, '707', $startDate, $endDate, 'credit');
        $achatsMarchandises = $this->getClassBalance($clientId, '607', $startDate, $endDate, 'debit');
        $margeCommerciale = $ventesMarchandises - $achatsMarchandises;

        // Production de l'exercice (70-76 sauf 707)
        $production = $this->getClassBalance($clientId, '70', $startDate, $endDate, 'credit')
            + $this->getClassBalance($clientId, '71', $startDate, $endDate, 'credit')
            + $this->getClassBalance($clientId, '72', $startDate, $endDate, 'credit')
            - $ventesMarchandises;

        // Consommation (60 sauf 607)
        $consommation = 0;
        foreach (['60', '61'] as $prefix) {
            $consommation += $this->getClassBalance($clientId, $prefix, $startDate, $endDate, 'debit');
        }
        $consommation -= $achatsMarchandises;

        // Valeur ajoutée
        $valeurAjoutee = $margeCommerciale + $production - $consommation;

        // EBE = VA - (charges de personnel 63 + impôts/taxes 64)
        $personnel = $this->getClassBalance($clientId, '63', $startDate, $endDate, 'debit');
        $impotsTaxes = $this->getClassBalance($clientId, '64', $startDate, $endDate, 'debit');
        $servicesExternes = $this->getClassBalance($clientId, '62', $startDate, $endDate, 'debit');
        $ebe = $valeurAjoutee - $personnel - $impotsTaxes - $servicesExternes;

        // Résultat d'exploitation = EBE + autres produits - autres charges
        $autresProduitsExp = $this->getClassBalance($clientId, '75', $startDate, $endDate, 'credit')
            + $this->getClassBalance($clientId, '76', $startDate, $endDate, 'credit');
        $autresChargesExp = $this->getClassBalance($clientId, '65', $startDate, $endDate, 'debit')
            + $this->getClassBalance($clientId, '66', $startDate, $endDate, 'debit');
        $dotations = $this->getClassBalance($clientId, '68', $startDate, $endDate, 'debit');
        $resultatExploitation = $ebe + $autresProduitsExp - $autresChargesExp - $dotations;

        return [
            'parameters' => [
                'client_id' => $clientId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'sig' => [
                'marge_commerciale' => round($margeCommerciale, 2),
                'production_exercice' => round($production, 2),
                'consommation' => round($consommation, 2),
                'valeur_ajoutee' => round($valeurAjoutee, 2),
                'excedent_brut_exploitation' => round($ebe, 2),
                'dotations_amortissements' => round($dotations, 2),
                'resultat_exploitation' => round($resultatExploitation, 2),
            ],
        ];
    }

    private function getClassBalance(int $clientId, string $codePrefix, string $startDate, string $endDate, string $side): float
    {
        return (float) DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->join('accounting_accounts', 'entry_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_accounts.client_id', $clientId)
            ->where('accounting_accounts.code', 'like', $codePrefix . '%')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '>=', $startDate)
            ->whereDate('journal_entries.entry_date', '<=', $endDate)
            ->sum('entry_lines.' . $side);
    }

    private function getAccountDetails(int $clientId, string $classPrefix, string $startDate, string $endDate, string $side): array
    {
        return DB::table('accounting_accounts')
            ->leftJoin('entry_lines', 'accounting_accounts.id', '=', 'entry_lines.account_id')
            ->leftJoin('journal_entries', function ($join) use ($clientId, $startDate, $endDate) {
                $join->on('entry_lines.entry_id', '=', 'journal_entries.id')
                    ->where('journal_entries.client_id', '=', $clientId)
                    ->where('journal_entries.status', '=', 'posted')
                    ->whereDate('journal_entries.entry_date', '>=', $startDate)
                    ->whereDate('journal_entries.entry_date', '<=', $endDate);
            })
            ->where('accounting_accounts.client_id', $clientId)
            ->where('accounting_accounts.code', 'like', $classPrefix . '%')
            ->where('accounting_accounts.is_active', true)
            ->groupBy('accounting_accounts.id', 'accounting_accounts.code', 'accounting_accounts.name')
            ->select([
                'accounting_accounts.id',
                'accounting_accounts.code',
                'accounting_accounts.name',
                DB::raw('COALESCE(SUM(entry_lines.' . $side . '), 0) as amount'),
            ])
            ->having('amount', '>', 0)
            ->orderBy('accounting_accounts.code')
            ->get()
            ->toArray();
    }
}
