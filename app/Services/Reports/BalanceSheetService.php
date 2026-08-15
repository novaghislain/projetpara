<?php
namespace App\Services\Reports;

use App\Models\AccountingAccount;
use Illuminate\Support\Facades\DB;

class BalanceSheetService
{
    /**
     * Génère le Bilan (Actif / Passif) selon SYSCOHADA
     *
     * ACTIF  : Classe 2 (Immobilisations), 3 (Stocks), 4 (Créances), 5 (Trésorerie)
     * PASSIF : Classe 1 (Capitaux propres, dettes)
     */
    public function generate(int $clientId, ?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $startDate = date('Y-01-01', strtotime($date));

        $actif = $this->getActif($clientId, $startDate, $date);
        $passif = $this->getPassif($clientId, $startDate, $date);
        $resultat = $this->getNetResult($clientId, $startDate, $date);

        $totalActif = $actif['total'];
        $totalPassif = $passif['total'] + $resultat;

        return [
            'parameters' => [
                'client_id' => $clientId,
                'as_of_date' => $date,
                'fiscal_year' => date('Y', strtotime($date)),
            ],
            'actif' => [
                'title' => 'ACTIF',
                'headings' => [
                    ['code' => '20', 'label' => 'Immobilisations incorporelles', 'accounts' => $actif['immobilisations_incorporelles']],
                    ['code' => '21', 'label' => 'Immobilisations corporelles', 'accounts' => $actif['immobilisations_corporelles']],
                    ['code' => '22-27', 'label' => 'Autres immobilisations', 'accounts' => $actif['autres_immobilisations']],
                    ['code' => '3', 'label' => 'Stocks', 'accounts' => $actif['stocks']],
                    ['code' => '4', 'label' => 'Créances & emplois', 'accounts' => $actif['creances']],
                    ['code' => '5', 'label' => 'Trésorerie', 'accounts' => $actif['tresorerie']],
                ],
                'total' => round($totalActif, 2),
            ],
            'passif' => [
                'title' => 'PASSIF',
                'headings' => [
                    ['code' => '10', 'label' => 'Capital & réserves', 'accounts' => $passif['capital_reserves']],
                    ['code' => '11-12', 'label' => 'Reports à nouveau & écarts', 'accounts' => $passif['reports_ecarts']],
                    ['code' => '13', 'label' => 'Résultat net', 'accounts' => ['value' => $resultat]],
                    ['code' => '14-16', 'label' => 'Emprunts & dettes financières', 'accounts' => $passif['emprunts']],
                    ['code' => '17-18', 'label' => 'Autres dettes', 'accounts' => $passif['autres_dettes']],
                ],
                'total' => round($totalPassif, 2),
            ],
            'resultat_net' => round($resultat, 2),
            'verification' => [
                'total_actif' => round($totalActif, 2),
                'total_passif' => round($totalPassif, 2),
                'difference' => round($totalActif - $totalPassif, 2),
                'is_balanced' => abs($totalActif - $totalPassif) < 1,
            ],
        ];
    }

    private function getActif(int $clientId, string $startDate, string $endDate): array
    {
        $result = [
            'immobilisations_incorporelles' => [],
            'immobilisations_corporelles' => [],
            'autres_immobilisations' => [],
            'stocks' => [],
            'creances' => [],
            'tresorerie' => [],
            'total' => 0,
        ];

        // Classe 2 : Immobilisations
        $comptes2 = $this->getAccountBalances($clientId, '2', $startDate, $endDate);
        foreach ($comptes2 as $compte) {
            $soldeBrut = $compte->balance_debit - $compte->balance_credit;
            // Amortissements/provisions (28xx, 29xx) réduisent l'actif
            if (str_starts_with($compte->code, '28') || str_starts_with($compte->code, '29')) {
                $soldeBrut = -abs($soldeBrut);
            }
            if ($soldeBrut <= 0) continue;

            $item = [
                'account_code' => $compte->code,
                'account_name' => $compte->name,
                'value' => round($soldeBrut, 2),
            ];

            if (str_starts_with($compte->code, '20')) {
                $result['immobilisations_incorporelles'][] = $item;
            } elseif (str_starts_with($compte->code, '21')) {
                $result['immobilisations_corporelles'][] = $item;
            } else {
                $result['autres_immobilisations'][] = $item;
            }
            $result['total'] += $soldeBrut;
        }

        // Classe 3 : Stocks
        $comptes3 = $this->getAccountBalances($clientId, '3', $startDate, $endDate);
        foreach ($comptes3 as $compte) {
            $solde = $compte->balance_debit - $compte->balance_credit;
            if ($solde <= 0) continue;
            $result['stocks'][] = [
                'account_code' => $compte->code,
                'account_name' => $compte->name,
                'value' => round($solde, 2),
            ];
            $result['total'] += $solde;
        }

        // Classe 4 : Créances (soldes débiteurs uniquement)
        $comptes4 = $this->getAccountBalances($clientId, '4', $startDate, $endDate);
        foreach ($comptes4 as $compte) {
            $solde = $compte->balance_debit - $compte->balance_credit;
            if ($solde <= 0) continue;
            $result['creances'][] = [
                'account_code' => $compte->code,
                'account_name' => $compte->name,
                'value' => round($solde, 2),
            ];
            $result['total'] += $solde;
        }

        // Classe 5 : Trésorerie
        $comptes5 = $this->getAccountBalances($clientId, '5', $startDate, $endDate);
        foreach ($comptes5 as $compte) {
            $solde = $compte->balance_debit - $compte->balance_credit;
            if ($solde <= 0) continue;
            $result['tresorerie'][] = [
                'account_code' => $compte->code,
                'account_name' => $compte->name,
                'value' => round($solde, 2),
            ];
            $result['total'] += $solde;
        }

        return $result;
    }

    private function getPassif(int $clientId, string $startDate, string $endDate): array
    {
        $result = [
            'capital_reserves' => [],
            'reports_ecarts' => [],
            'emprunts' => [],
            'autres_dettes' => [],
            'total' => 0,
        ];

        $comptes1 = $this->getAccountBalances($clientId, '1', $startDate, $endDate);
        foreach ($comptes1 as $compte) {
            $solde = $compte->balance_credit - $compte->balance_debit;

            // Le résultat (classe 13) est traité séparément
            if (str_starts_with($compte->code, '13')) continue;
            if ($solde <= 0) continue;

            $item = [
                'account_code' => $compte->code,
                'account_name' => $compte->name,
                'value' => round($solde, 2),
            ];

            if (str_starts_with($compte->code, '10')) {
                $result['capital_reserves'][] = $item;
            } elseif (str_starts_with($compte->code, '11') || str_starts_with($compte->code, '12')) {
                $result['reports_ecarts'][] = $item;
            } elseif (preg_match('/^1[456]/', $compte->code)) {
                $result['emprunts'][] = $item;
            } else {
                $result['autres_dettes'][] = $item;
            }
            $result['total'] += $solde;
        }

        return $result;
    }

    /**
     * Résultat net : classe 7 (produits) - classe 6 (charges)
     */
    private function getNetResult(int $clientId, string $startDate, string $endDate): float
    {
        // D'abord via le compte 13 (Résultat)
        $resultQuery = DB::table('gel_lignes_ecriture as gl')
            ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
            ->join('gel_account_types as gat', 'gl.compte_id', '=', 'gat.id')
            ->where('gat.code', 'like', '13%')
            ->where('ge.client_id', $clientId)
            ->where('ge.valide', true)
            ->whereDate('ge.date_ecriture', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(CASE WHEN gl.sens = "debit" THEN gl.montant ELSE 0 END), 0) as total_debit, COALESCE(SUM(CASE WHEN gl.sens = "credit" THEN gl.montant ELSE 0 END), 0) as total_credit')
            ->first();

        $resultByAccount = (float) ($resultQuery->total_credit ?? 0) - (float) ($resultQuery->total_debit ?? 0);

        if (abs($resultByAccount) < 0.01) {
            // Calcul par différence Produits - Charges
            $charges = (float) DB::table('gel_lignes_ecriture as gl')
                ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
                ->join('gel_account_types as gat', 'gl.compte_id', '=', 'gat.id')
                ->where('gat.code', 'like', '6%')
                ->where('ge.client_id', $clientId)
                ->where('ge.valide', true)
                ->whereDate('ge.date_ecriture', '>=', $startDate)
                ->whereDate('ge.date_ecriture', '<=', $endDate)
                ->where('gl.sens', 'debit')
                ->sum('gl.montant');

            $produits = (float) DB::table('gel_lignes_ecriture as gl')
                ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
                ->join('gel_account_types as gat', 'gl.compte_id', '=', 'gat.id')
                ->where('gat.code', 'like', '7%')
                ->where('ge.client_id', $clientId)
                ->where('ge.valide', true)
                ->whereDate('ge.date_ecriture', '>=', $startDate)
                ->whereDate('ge.date_ecriture', '<=', $endDate)
                ->where('gl.sens', 'credit')
                ->sum('gl.montant');

            return round($produits - $charges, 2);
        }

        return round($resultByAccount, 2);
    }

    private function getAccountBalances(int $clientId, string $classPrefix, string $startDate, string $endDate): array
    {
        return DB::table('gel_account_types as gat')
            ->join('gel_lignes_ecriture as gl', 'gat.id', '=', 'gl.compte_id')
            ->join('gel_ecritures as ge', function ($join) use ($clientId, $startDate, $endDate) {
                $join->on('gl.ecriture_id', '=', 'ge.id')
                    ->where('ge.client_id', '=', $clientId)
                    ->where('ge.valide', '=', true)
                    ->whereDate('ge.date_ecriture', '>=', $startDate)
                    ->whereDate('ge.date_ecriture', '<=', $endDate);
            })
            ->where('gat.code', 'like', $classPrefix . '%')
            ->groupBy('gat.id', 'gat.code', 'gat.libelle')
            ->select([
                'gat.id',
                'gat.code',
                'gat.libelle as name',
                DB::raw('COALESCE(SUM(CASE WHEN gl.sens = "debit" THEN gl.montant ELSE 0 END), 0) as balance_debit'),
                DB::raw('COALESCE(SUM(CASE WHEN gl.sens = "credit" THEN gl.montant ELSE 0 END), 0) as balance_credit'),
            ])
            ->orderBy('gat.code')
            ->get()
            ->toArray();
    }
}
