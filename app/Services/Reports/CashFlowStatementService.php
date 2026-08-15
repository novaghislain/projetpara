<?php
namespace App\Services\Reports;

use App\Models\AccountingAccount;
use Illuminate\Support\Facades\DB;

class CashFlowStatementService
{
    /**
     * Tableau de Flux de Trésorerie (méthode indirecte)
     *
     * Basé sur le résultat net et les variations du BFR
     */
    public function generate(int $clientId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-01-01');
        $endDate = $endDate ?? date('Y-m-d');
        $prevStartDate = date('Y-m-d', strtotime($startDate . ' -1 year'));
        $prevEndDate = date('Y-m-d', strtotime($endDate . ' -1 year'));

        // 1. Résultat net
        $incomeService = new IncomeStatementService();
        $income = $incomeService->generate($clientId, $startDate, $endDate);
        $resultatNet = $income['resultat_net'];

        // 2. Ajustements non monétaires
        $dotationsAmortissements = $this->getClassBalance($clientId, '68', $startDate, $endDate, 'debit');
        $reprisesProvisions = $this->getClassBalance($clientId, '78', $startDate, $endDate, 'credit');

        // 3. Variation du BFR
        $bfrCurrent = $this->calculateBFR($clientId, $endDate);
        $bfrPrevious = $this->calculateBFR($clientId, $prevEndDate);
        $variationBFR = $bfrCurrent - $bfrPrevious;

        // 4. Flux d'exploitation
        $fluxExploitation = $resultatNet + $dotationsAmortissements - $reprisesProvisions - $variationBFR;

        // 5. Flux d'investissement
        $acquisitions = 0;
        foreach (['20', '21', '22', '23', '24', '25', '26', '27'] as $prefix) {
            $acquisitions += $this->getClassBalance($clientId, $prefix, $startDate, $endDate, 'debit');
        }
        $cessions = $this->getClassBalance($clientId, '75', $startDate, $endDate, 'credit');
        $fluxInvestissement = -$acquisitions + $cessions;

        // 6. Flux de financement
        $augmentationCapital = $this->getClassBalance($clientId, '101', $startDate, $endDate, 'credit');
        $empruntsNouveaux = $this->getClassBalance($clientId, '16', $startDate, $endDate, 'credit');
        $remboursements = $this->getClassBalance($clientId, '16', $startDate, $endDate, 'debit');
        $dividendes = $this->getClassBalance($clientId, '67', $startDate, $endDate, 'debit');
        $fluxFinancement = $augmentationCapital + $empruntsNouveaux - $remboursements - $dividendes;

        $variationTresorerie = $fluxExploitation + $fluxInvestissement + $fluxFinancement;

        // 7. Trésorerie d'ouverture / clôture
        $tresorerieOuverture = $this->getTresorerieBalance($clientId, $startDate);
        $tresorerieCloture = $this->getTresorerieBalance($clientId, $endDate);

        return [
            'parameters' => [
                'client_id' => $clientId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'resultat_net' => round($resultatNet, 2),
            'ajustements' => [
                'dotations_amortissements' => round($dotationsAmortissements, 2),
                'reprises_provisions' => round($reprisesProvisions, 2),
                'total_ajustements' => round($dotationsAmortissements - $reprisesProvisions, 2),
            ],
            'variation_bfr' => [
                'bfr_ouverture' => round($bfrPrevious, 2),
                'bfr_cloture' => round($bfrCurrent, 2),
                'variation' => round($variationBFR, 2),
            ],
            'flux_tresorerie' => [
                'exploitation' => round($fluxExploitation, 2),
                'investissement' => round($fluxInvestissement, 2),
                'financement' => round($fluxFinancement, 2),
                'variation_totale' => round($variationTresorerie, 2),
            ],
            'tresorerie' => [
                'ouverture' => round($tresorerieOuverture, 2),
                'cloture' => round($tresorerieCloture, 2),
                'variation' => round($tresorerieCloture - $tresorerieOuverture, 2),
            ],
        ];
    }

    private function getClassBalance(int $clientId, string $codePrefix, string $startDate, string $endDate, string $side): float
    {
        return (float) DB::table('gel_lignes_ecriture as gl')
            ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
            ->join('gel_account_types as gat', 'gl.compte_id', '=', 'gat.id')
            ->where('ge.client_id', $clientId)
            ->where('gat.code', 'like', $codePrefix . '%')
            ->where('ge.valide', true)
            ->whereDate('ge.date_ecriture', '>=', $startDate)
            ->whereDate('ge.date_ecriture', '<=', $endDate)
            ->where('gl.sens', $side)
            ->sum('gl.montant');
    }

    /**
     * BFR = Actif circulant (classe 3 + 4 débiteurs) - Passif circulant (classe 40 créditeurs)
     */
    private function calculateBFR(int $clientId, string $date): float
    {
        $actifCirculant = (float) DB::table('gel_lignes_ecriture as gl')
            ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
            ->join('gel_account_types as gat', 'gl.compte_id', '=', 'gat.id')
            ->where('ge.client_id', $clientId)
            ->where(function ($q) {
                $q->where('gat.code', 'like', '3%')
                  ->orWhere('gat.code', 'like', '4%');
            })
            ->where('ge.valide', true)
            ->whereDate('ge.date_ecriture', '<=', $date)
            ->selectRaw('COALESCE(SUM(CASE WHEN gl.sens = "debit" THEN gl.montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN gl.sens = "credit" THEN gl.montant ELSE 0 END), 0) as balance')
            ->value('balance');

        $passifCirculant = (float) DB::table('gel_lignes_ecriture as gl')
            ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
            ->join('gel_account_types as gat', 'gl.compte_id', '=', 'gat.id')
            ->where('ge.client_id', $clientId)
            ->where('gat.code', 'like', '40%')
            ->where('ge.valide', true)
            ->whereDate('ge.date_ecriture', '<=', $date)
            ->selectRaw('COALESCE(SUM(CASE WHEN gl.sens = "credit" THEN gl.montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN gl.sens = "debit" THEN gl.montant ELSE 0 END), 0) as balance')
            ->value('balance');

        return $actifCirculant - $passifCirculant;
    }

    /**
     * Trésorerie nette = classe 5 (solde débiteur)
     */
    private function getTresorerieBalance(int $clientId, string $date): float
    {
        $tresorerie = (float) DB::table('gel_lignes_ecriture as gl')
            ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
            ->join('gel_account_types as gat', 'gl.compte_id', '=', 'gat.id')
            ->where('ge.client_id', $clientId)
            ->where('gat.code', 'like', '5%')
            ->where('ge.valide', true)
            ->whereDate('ge.date_ecriture', '<=', $date)
            ->selectRaw('COALESCE(SUM(CASE WHEN gl.sens = "debit" THEN gl.montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN gl.sens = "credit" THEN gl.montant ELSE 0 END), 0) as balance')
            ->value('balance');

        return (float) $tresorerie;
    }
}
