<?php
namespace App\Services\Reports;

use App\Models\AccountingAccount;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    /**
     * Balance de vérification : Total Débits = Total Crédits
     */
    public function generate(int $clientId, ?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $accounts = DB::table('gel_account_types as gat')
            ->join('gel_lignes_ecriture as gl', 'gat.id', '=', 'gl.compte_id')
            ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
            ->where('ge.client_id', $clientId)
            ->where('ge.valide', true)
            ->whereDate('ge.date_ecriture', '<=', $date)
            ->select([
                'gat.id as account_id',
                'gat.code',
                'gat.libelle as name',
                'gat.classe',
                DB::raw('COALESCE(SUM(CASE WHEN gl.sens = "debit" THEN gl.montant ELSE 0 END), 0) as debit'),
                DB::raw('COALESCE(SUM(CASE WHEN gl.sens = "credit" THEN gl.montant ELSE 0 END), 0) as credit')
            ])
            ->groupBy('gat.id', 'gat.code', 'gat.libelle', 'gat.classe')
            ->orderBy('gat.code')
            ->get();

        $lines = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $debit = (float) $account->debit;
            $credit = (float) $account->credit;

            if ($debit == 0 && $credit == 0) continue;

            $lines[] = [
                'account_id' => $account->account_id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'class' => $account->classe ?? substr($account->code, 0, 1),
                'total_debit' => $debit,
                'total_credit' => $credit,
                'balance' => round($debit - $credit, 2),
            ];

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return [
            'parameters' => [
                'client_id' => $clientId,
                'as_of_date' => $date,
            ],
            'lines' => $lines,
            'totals' => [
                'total_debit' => round($totalDebit, 2),
                'total_credit' => round($totalCredit, 2),
                'difference' => round($totalDebit - $totalCredit, 2),
                'is_balanced' => abs(round($totalDebit, 2) - round($totalCredit, 2)) < 0.01,
            ],
            'summary' => [
                'total_accounts' => count($lines),
                'debit_balances' => count(array_filter($lines, fn($l) => $l['balance'] > 0)),
                'credit_balances' => count(array_filter($lines, fn($l) => $l['balance'] < 0)),
                'zero_balances' => count(array_filter($lines, fn($l) => $l['balance'] == 0)),
            ],
        ];
    }

    /**
     * Balance âgée (Aging) clients/fournisseurs
     */
    public function generateAging(int $clientId, string $type = 'customer', ?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');

        // Comptes clients (41) ou fournisseurs (40)
        $prefix = $type === 'customer' ? '41' : '40';

        $accounts = DB::table('gel_account_types')
            ->where('code', 'like', $prefix . '%')
            ->get();

        $result = [];

        foreach ($accounts as $account) {
            $entries = DB::table('gel_lignes_ecriture as gl')
                ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
                ->where('gl.compte_id', $account->id)
                ->where('ge.client_id', $clientId)
                ->where('ge.valide', true)
                ->whereDate('ge.date_ecriture', '<=', $asOfDate)
                ->select('ge.date_ecriture as entry_date', 'ge.reference', 'gl.sens', 'gl.montant')
                ->orderBy('ge.date_ecriture')
                ->get();

            $solde = 0;
            foreach ($entries as $entry) {
                if ($entry->sens === 'debit') {
                    $solde += (float) $entry->montant;
                } else {
                    $solde -= (float) $entry->montant;
                }
            }

            if (abs($solde) < 0.01) continue;

            $balance = abs($solde);
            $agingBuckets = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '91_plus' => 0];

            foreach ($entries as $entry) {
                $days = (int) ((strtotime($asOfDate) - strtotime($entry->entry_date)) / 86400);
                $amount = (float) $entry->montant;
                
                if ($type === 'customer' && $entry->sens !== 'debit') continue;
                if ($type !== 'customer' && $entry->sens !== 'credit') continue;

                if ($days <= 30) $agingBuckets['0_30'] += $amount;
                elseif ($days <= 60) $agingBuckets['31_60'] += $amount;
                elseif ($days <= 90) $agingBuckets['61_90'] += $amount;
                else $agingBuckets['91_plus'] += $amount;
            }

            $result[] = [
                'account_code' => $account->code,
                'account_name' => $account->libelle,
                'balance' => round($solde, 2),
                'aging' => [
                    '0_30' => round($agingBuckets['0_30'], 2),
                    '31_60' => round($agingBuckets['31_60'], 2),
                    '61_90' => round($agingBuckets['61_90'], 2),
                    '91_plus' => round($agingBuckets['91_plus'], 2),
                ],
            ];
        }

        return [
            'type' => $type,
            'as_of_date' => $asOfDate,
            'accounts' => $result,
            'total' => count($result),
        ];
    }
}
