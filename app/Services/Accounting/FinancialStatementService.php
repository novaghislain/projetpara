<?php

namespace App\Services\Accounting;

use App\Models\AccountingAccount;
use Illuminate\Support\Facades\DB;

class FinancialStatementService
{
    protected BalanceCalculationService $balanceService;

    public function __construct(BalanceCalculationService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Bilan Actif/Passif à une date donnée.
     * Règles SYSCOHADA : Actif = Classe 2 + 3 + 4 (débiteurs) + 5 ; Passif = Classe 1 + 4 (créditeurs)
     */
    public function bilan(int $clientId, int $fiscalYearId): array
    {
        $fiscalYear = \App\Models\FiscalYear::findOrFail($fiscalYearId);
        $soldes = $this->balanceService->soldesAu($fiscalYear->date_end, $clientId, $fiscalYearId);

        $actif = [
            'immobilisations_incorporelles' => $this->filtrerSoldes($soldes, '21'),
            'immobilisations_corporelles'    => $this->filtrerSoldes($soldes, ['22', '23', '24', '25', '26']),
            'immobilisations_en_cours'       => $this->filtrerSoldes($soldes, '27'),
            'amortissements'                 => $this->filtrerSoldes($soldes, '28'),
            'depreciations_actif'            => $this->filtrerSoldes($soldes, '29'),
            'stocks'                         => $this->filtrerSoldes($soldes, ['31', '32', '33', '34', '35']),
            'depreciations_stocks'           => $this->filtrerSoldes($soldes, '39'),
            'creances_clients'               => $this->filtrerSoldes($soldes, ['411', '412', '418']),
            'autres_creances'                => $this->filtrerSoldes($soldes, ['409', '424', '449', '461', '467', '474', '481']),
            'depreciations_tiers'            => $this->filtrerSoldes($soldes, '49'),
            'tresorerie_actif'               => $this->filtrerSoldes($soldes, ['51', '52', '53', '54', '55', '56']),
            'comptes_regularisation'         => $this->filtrerSoldes($soldes, ['471', '473']),
        ];

        $passif = [
            'capital'                    => $this->filtrerSoldes($soldes, ['101', '102']),
            'reserves'                   => $this->filtrerSoldes($soldes, '106'),
            'report_nouveau'             => $this->filtrerSoldes($soldes, '1071'),
            'resultat_exercice'          => $this->filtrerSoldes($soldes, '109'),
            'emprunts_dettes_financieres' => $this->filtrerSoldes($soldes, ['132', '138']),
            'fournisseurs'               => $this->filtrerSoldes($soldes, ['401', '402', '408']),
            'dettes_sociales'            => $this->filtrerSoldes($soldes, ['42', '43']),
            'dettes_fiscales'            => $this->filtrerSoldes($soldes, ['44']),
            'autres_dettes'              => $this->filtrerSoldes($soldes, ['45', '462', '468']),
            'provisions_risques'         => $this->filtrerSoldes($soldes, ['141', '148']),
            'comptes_regularisation_passif' => $this->filtrerSoldes($soldes, ['472', '474']),
        ];

        $totalActif = $this->totalRubriques($actif);
        $totalPassif = $this->totalRubriques($passif);

        return [
            'actif'  => ['rubriques' => $actif, 'total' => $totalActif],
            'passif' => ['rubriques' => $passif, 'total' => $totalPassif],
            'date'   => $fiscalYear->date_end->format('Y-m-d'),
        ];
    }

    /**
     * Compte de résultat selon SYSCOHADA.
     */
    public function compteResultat(int $clientId, int $fiscalYearId): array
    {
        $fiscalYear = \App\Models\FiscalYear::findOrFail($fiscalYearId);
        $soldes = $this->balanceService->soldesAu($fiscalYear->date_end, $clientId, $fiscalYearId);

        $charges = [
            'achats'                        => $this->filtrerSoldes($soldes, ['601', '602', '603', '604', '605']),
            'services_exterieurs'           => $this->filtrerSoldes($soldes, ['611', '612', '613', '614', '615', '616', '617', '618', '619']),
            'autres_services'               => $this->filtrerSoldes($soldes, ['621', '622', '623', '624', '628']),
            'impots_taxes'                  => $this->filtrerSoldes($soldes, ['63']),
            'charges_personnel'             => $this->filtrerSoldes($soldes, ['64']),
            'frais_financiers'              => $this->filtrerSoldes($soldes, ['65']),
            'dotations_amortissements'      => $this->filtrerSoldes($soldes, ['66']),
            'dotations_provisions'          => $this->filtrerSoldes($soldes, ['67']),
            'autres_charges'                => $this->filtrerSoldes($soldes, ['68']),
            'participation_travailleurs'    => $this->filtrerSoldes($soldes, ['691']),
        ];

        $produits = [
            'ventes_marchandises'           => $this->filtrerSoldes($soldes, ['701', '702', '703', '704']),
            'subventions'                   => $this->filtrerSoldes($soldes, ['711']),
            'produits_financiers'           => $this->filtrerSoldes($soldes, ['72']),
            'autres_produits'               => $this->filtrerSoldes($soldes, ['73', '75', '781']),
        ];

        $totalCharges = $this->totalRubriques($charges);
        $totalProduits = $this->totalRubriques($produits);
        $resultatNet = round($totalProduits - $totalCharges, 2);

        return [
            'charges'  => ['rubriques' => $charges, 'total' => $totalCharges],
            'produits' => ['rubriques' => $produits, 'total' => $totalProduits],
            'resultat_net' => $resultatNet,
            'resultat_label' => $resultatNet >= 0 ? 'BÉNÉFICE' : 'PERTE',
            'date' => $fiscalYear->date_end->format('Y-m-d'),
        ];
    }

    /**
     * SIG — Soldes Intermédiaires de Gestion.
     */
    public function sig(int $clientId, int $fiscalYearId): array
    {
        $soldes = $this->balanceService->soldesAu(
            \App\Models\FiscalYear::findOrFail($fiscalYearId)->date_end,
            $clientId,
            $fiscalYearId
        );

        $ventes = $this->soldeCode($soldes, ['701', '702', '703', '704', '705']);
        $achats = $this->soldeCode($soldes, ['601', '602', '603', '604', '605', '608']);
        $servicesExternes = $this->soldeCode($soldes, ['61', '62']);
        $impotsTaxes = $this->soldeCode($soldes, '63');
        $chargesPersonnel = $this->soldeCode($soldes, '64');
        $dotations = $this->soldeCode($soldes, ['66', '67']);
        $autresCharges = $this->soldeCode($soldes, ['68', '691']);
        $produitsFinanciers = $this->soldeCode($soldes, '72');
        $autresProduits = $this->soldeCode($soldes, ['73', '75', '781']);
        $fraisFinanciers = $this->soldeCode($soldes, '65');

        $margeCommerciale = round($ventes - $achats, 2);
        $valeurAjoutee = round($margeCommerciale - $servicesExternes, 2);
        $ebitda = round($valeurAjoutee - $impotsTaxes - $chargesPersonnel, 2);
        $resultatExploitation = round($ebitda - $dotations, 2);
        $resultatAvantImpots = round($resultatExploitation + $produitsFinanciers - $fraisFinanciers + $autresProduits - $autresCharges, 2);

        return [
            'ventes' => $ventes,
            'achats' => $achats,
            'marge_commerciale' => $margeCommerciale,
            'services_externes' => $servicesExternes,
            'valeur_ajoutee' => $valeurAjoutee,
            'impots_taxes' => $impotsTaxes,
            'charges_personnel' => $chargesPersonnel,
            'ebitda' => $ebitda,
            'dotations' => $dotations,
            'resultat_exploitation' => $resultatExploitation,
            'produits_financiers' => $produitsFinanciers,
            'frais_financiers' => $fraisFinanciers,
            'autres_produits' => $autresProduits,
            'autres_charges' => $autresCharges,
            'resultat_avant_impots' => $resultatAvantImpots,
        ];
    }

    /**
     * Grand Livre pour un compte ou tous les comptes.
     */
    public function grandLivre(int $clientId, int $fiscalYearId, ?int $accountId = null): \Illuminate\Support\Collection
    {
        $query = \App\Models\AccountingJournalLine::select([
                'accounting_journals.id as journal_id',
                'accounting_journals.entry_date',
                'accounting_journals.reference',
                'accounting_journals.numero_piece',
                'accounting_journals.journal_type',
                'accounting_journals.description as journal_description',
                'accounting_accounts.id as account_id',
                'accounting_accounts.code',
                'accounting_accounts.name',
                'accounting_journal_lines.label',
                \DB::raw('COALESCE(accounting_journal_lines.debit, 0) as debit'),
                \DB::raw('COALESCE(accounting_journal_lines.credit, 0) as credit'),
            ])
            ->join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->join('accounting_accounts', 'accounting_journal_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.fiscal_year_id', $fiscalYearId)
            ->where('accounting_journals.status', 'posted')
            ->orderBy('accounting_accounts.code')
            ->orderBy('accounting_journals.entry_date')
            ->orderBy('accounting_journals.id');

        if ($accountId) {
            $query->where('accounting_journal_lines.account_id', $accountId);
        }

        return $query->get();
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function filtrerSoldes($soldes, $codes): array
    {
        if (!is_array($codes)) $codes = [$codes];
        $items = $soldes->filter(fn($s) =>
            collect($codes)->contains(fn($c) => str_starts_with($s->code, $c))
        );
        return [
            'montant' => round($items->sum('solde'), 2),
            'details' => $items->values(),
        ];
    }

    private function soldeCode($soldes, $codes): float
    {
        if (!is_array($codes)) $codes = [$codes];
        return round($soldes->filter(fn($s) =>
            collect($codes)->contains(fn($c) => str_starts_with($s->code, $c))
        )->sum('solde'), 2);
    }

    private function totalRubriques(array $rubriques): float
    {
        return round(collect($rubriques)->sum(fn($r) => $r['montant']), 2);
    }
}
