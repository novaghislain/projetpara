<?php

namespace App\Services\Accounting;

use App\Models\AccountingTaxDeclaration;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use App\Models\FiscalYear;
use Illuminate\Support\Facades\DB;

class TaxCalculationService
{
    /**
     * Taux fiscaux Bénin (CGI 2025/2026)
     */
    const TAUX_TVA = 18.00;
    const TAUX_IS = 30.00;
    const TAUX_VPS = 4.00;
    const TAUX_CNSS_EMPLOYEUR = 3.60;
    const TAUX_CNSS_SALARIE = 10.00; // Part salariale en % du brut

    /**
     * Barème ITS (Impôt sur les Traitements et Salaires) - Bénin 2025/2026
     * Tranches annuelles en FCFA
     */
    const ITS_BAREME = [
        ['min' => 0,       'max' => 300000,   'taux' => 0,   'cumul' => 0],
        ['min' => 300001,  'max' => 2000000,  'taux' => 10,  'cumul' => 0],
        ['min' => 2000001, 'max' => 5000000,  'taux' => 20,  'cumul' => 170000],
        ['min' => 5000001, 'max' => INF,      'taux' => 30,  'cumul' => 770000],
    ];

    /**
     * Calculer la TVA à partir des écritures comptables.
     */
    public function calculerTva(int $clientId, int $fiscalYearId, int $month): array
    {
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        // Comptes TVA selon SYSCOHADA
        $tvaCollectee = $this->getSoldeCompte($clientId, $fiscalYearId, '4411', $fiscalYear, $month);
        $tvaRecuperable = $this->getSoldeCompte($clientId, $fiscalYearId, '4412', $fiscalYear, $month);

        $tvaCollectee = abs($tvaCollectee);
        $tvaRecuperable = abs($tvaRecuperable);
        $tvaNet = round($tvaCollectee - $tvaRecuperable, 2);

        return [
            'base_imposable' => round($tvaNet > 0 ? $tvaNet / (self::TAUX_TVA / 100) : 0, 2),
            'tva_collectee' => $tvaCollectee,
            'tva_recuperable' => $tvaRecuperable,
            'tva_net' => $tvaNet,
            'credit_tva' => $tvaNet < 0 ? abs($tvaNet) : 0,
            'taux' => self::TAUX_TVA,
        ];
    }

    /**
     * Calculer l'IS (Impôt sur les Sociétés) — 30% du résultat fiscal.
     */
    public function calculerIs(int $clientId, int $fiscalYearId): array
    {
        $resultatFiscal = $this->getResultatFiscal($clientId, $fiscalYearId);
        $isBrut = round($resultatFiscal * (self::TAUX_IS / 100), 2);
        $acomptesVerses = $this->getAcomptesIs($clientId, $fiscalYearId);
        $soldeIs = round($isBrut - $acomptesVerses, 2);

        return [
            'resultat_fiscal' => $resultatFiscal,
            'taux' => self::TAUX_IS,
            'is_brut' => $isBrut,
            'acomptes_verses' => $acomptesVerses,
            'solde' => $soldeIs,
        ];
    }

    /**
     * Calculer l'ITS (Impôt sur les Traitements et Salaires).
     * @param float $salaireBrutAnnuel Salaire brut annuel en FCFA
     */
    public function calculerIts(float $salaireBrutAnnuel): array
    {
        $impots = 0;
        $tranches = [];

        foreach (self::ITS_BAREME as $tranche) {
            if ($salaireBrutAnnuel > $tranche['min']) {
                $baseTranche = min($salaireBrutAnnuel, $tranche['max']) - $tranche['min'];
                $impotTranche = round($baseTranche * $tranche['taux'] / 100, 2);
                $impots += $impotTranche;
                $tranches[] = [
                    'min' => $tranche['min'],
                    'max' => $tranche['max'],
                    'base' => round($baseTranche, 2),
                    'taux' => $tranche['taux'],
                    'impot' => $impotTranche,
                ];
            }
        }

        $salaireNet = round($salaireBrutAnnuel - $impots, 2);
        $tauxEffectif = $salaireBrutAnnuel > 0 ? round(($impots / $salaireBrutAnnuel) * 100, 2) : 0;

        return [
            'salaire_brut' => $salaireBrutAnnuel,
            'tranches' => $tranches,
            'total_impot' => $impots,
            'salaire_net' => $salaireNet,
            'taux_effectif' => $tauxEffectif,
        ];
    }

    /**
     * Calculer CNSS (Caisse Nationale de Sécurité Sociale).
     */
    public function calculerCnss(float $salaireBrutMensuel): array
    {
        $plafond = 2000000; // Plafond CNSS Bénin (à confirmer)
        $assiette = min($salaireBrutMensuel, $plafond);

        $partEmployeur = round($assiette * (self::TAUX_CNSS_EMPLOYEUR / 100), 2);
        $partSalarie = round($assiette * (self::TAUX_CNSS_SALARIE / 100), 2);

        return [
            'assiette' => $assiette,
            'part_employeur' => $partEmployeur,
            'part_salarie' => $partSalarie,
            'total' => round($partEmployeur + $partSalarie, 2),
            'taux_employeur' => self::TAUX_CNSS_EMPLOYEUR,
            'taux_salarie' => self::TAUX_CNSS_SALARIE,
        ];
    }

    /**
     * Calculer VPS (Versement Patronal sur Salaires).
     */
    public function calculerVps(float $masseSalariale): array
    {
        $vps = round($masseSalariale * (self::TAUX_VPS / 100), 2);
        return [
            'masse_salariale' => $masseSalariale,
            'taux' => self::TAUX_VPS,
            'montant' => $vps,
        ];
    }

    /**
     * Calculer l'AIB (Acompte IS) — 1% du CA pour les entreprises au réel simplifié.
     */
    public function calculerAib(float $chiffreAffaires, float $taux = 1.0): array
    {
        return [
            'chiffre_affaires' => $chiffreAffaires,
            'taux' => $taux,
            'montant' => round($chiffreAffaires * ($taux / 100), 2),
        ];
    }

    /**
     * Générer et enregistrer une déclaration fiscale complète.
     */
    public function genererDeclaration(
        int $clientId, int $fiscalYearId, string $taxType,
        string $periodType, ?int $periodMonth, ?int $periodQuarter, int $periodYear,
        array $data
    ): AccountingTaxDeclaration {
        $count = AccountingTaxDeclaration::where('client_id', $clientId)
            ->where('tax_type', $taxType)
            ->where('period_year', $periodYear)
            ->count();

        $reference = sprintf('%s-%s-%04d-%03d', strtoupper($taxType), $fiscalYearId, $periodYear, $count + 1);
        $dateEcheance = $this->getDateEcheance($taxType, $periodYear, $periodMonth);

        return AccountingTaxDeclaration::create([
            'client_id' => $clientId,
            'fiscal_year_id' => $fiscalYearId,
            'tax_type' => $taxType,
            'reference' => $reference,
            'period_type' => $periodType,
            'period_month' => $periodMonth,
            'period_quarter' => $periodQuarter,
            'period_year' => $periodYear,
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'date_echeance' => $dateEcheance,
            'base_imposable' => $data['base_imposable'] ?? 0,
            'taux' => $data['taux'] ?? 0,
            'montant_dut' => $data['montant_dut'] ?? 0,
            'montant_paye' => 0,
            'solde' => $data['montant_dut'] ?? 0,
            'tva_collectee' => $data['tva_collectee'] ?? null,
            'tva_recuperable' => $data['tva_recuperable'] ?? null,
            'tva_net' => $data['tva_net'] ?? null,
            'credit_tva' => $data['credit_tva'] ?? null,
            'resultat_fiscal' => $data['resultat_fiscal'] ?? null,
            'status' => 'calcule',
        ]);
    }

    // ─── Helpers privés ──────────────────────────────────────

    private function getSoldeCompte(int $clientId, int $fiscalYearId, string $code, $fiscalYear, int $month): float
    {
        $dateDebut = now()->setYear($fiscalYear->year)->setMonth($month)->startOfMonth();
        $dateFin = min(
            $fiscalYear->date_end,
            now()->setYear($fiscalYear->year)->setMonth($month)->lastOfMonth()
        );

        $result = AccountingJournalLine::join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->join('accounting_accounts', 'accounting_journal_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.fiscal_year_id', $fiscalYearId)
            ->where('accounting_journals.status', 'posted')
            ->where('accounting_accounts.code', $code)
            ->where('accounting_journals.entry_date', '>=', $dateDebut)
            ->where('accounting_journals.entry_date', '<=', $dateFin)
            ->select([
                DB::raw('SUM(COALESCE(debit, 0)) as debit'),
                DB::raw('SUM(COALESCE(credit, 0)) as credit'),
            ])
            ->first();

        return round(($result->debit ?? 0) - ($result->credit ?? 0), 2);
    }

    private function getResultatFiscal(int $clientId, int $fiscalYearId): float
    {
        $service = app(FinancialStatementService::class);
        $resultat = $service->compteResultat($clientId, $fiscalYearId);
        return $resultat['resultat_net'];
    }

    private function getAcomptesIs(int $clientId, int $fiscalYearId): float
    {
        $result = AccountingJournalLine::join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->join('accounting_accounts', 'accounting_journal_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.fiscal_year_id', $fiscalYearId)
            ->where('accounting_journals.status', 'posted')
            ->where('accounting_accounts.code', '4422')
            ->select(DB::raw('SUM(COALESCE(debit, 0)) as total'))
            ->first();

        return round($result->total ?? 0, 2);
    }

    private function getDateEcheance(string $taxType, int $year, ?int $month): string
    {
        $echeances = [
            'tva'  => $month ? "{$year}-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "-15" : "{$year}-01-15",
            'is'   => "{$year}-03-31",
            'its'  => $month ? "{$year}-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "-15" : "{$year}-01-15",
            'cnss' => $month ? "{$year}-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "-15" : "{$year}-01-15",
            'vps'  => $month ? "{$year}-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "-15" : "{$year}-01-15",
        ];
        return $echeances[$taxType] ?? "{$year}-12-31";
    }
}
