<?php
namespace App\Services\Reports;

use App\Models\AccountingAccount;
use Illuminate\Support\Facades\DB;

class BalanceReportService
{
    /**
     * Génère la balance générale (tous comptes avec soldes)
     *
     * @param int $clientId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $classFilter (1,2,3,4,5,6,7,8)
     * @param array|null $accountIds
     * @return array
     */
    public function generate(
        int $clientId,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $classFilter = null,
        ?array $accountIds = null
    ): array {
        $query = DB::table('gel_account_types');

        if ($classFilter) {
            $query->where('code', 'like', $classFilter . '%');
        }

        if ($accountIds) {
            $query->whereIn('id', $accountIds);
        }

        $accounts = $query->orderBy('code')->get();

        $startDate = $startDate ?? date('Y-01-01');
        $endDate = $endDate ?? date('Y-m-d');

        $results = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $totalBalanceDebit = 0;
        $totalBalanceCredit = 0;

        foreach ($accounts as $account) {
            // Solde d'ouverture (avant startDate)
            $openingQuery = DB::table('gel_lignes_ecriture as gl')
                ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
                ->where('gl.compte_id', $account->id)
                ->where('ge.client_id', $clientId)
                ->where('ge.valide', true)
                ->whereDate('ge.date_ecriture', '<', $startDate);

            $openingDebit = (float) (clone $openingQuery)->where('gl.sens', 'debit')->sum('gl.montant');
            $openingCredit = (float) (clone $openingQuery)->where('gl.sens', 'credit')->sum('gl.montant');

            // Mouvements de la période
            $movementQuery = DB::table('gel_lignes_ecriture as gl')
                ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
                ->where('gl.compte_id', $account->id)
                ->where('ge.client_id', $clientId)
                ->where('ge.valide', true)
                ->whereDate('ge.date_ecriture', '>=', $startDate)
                ->whereDate('ge.date_ecriture', '<=', $endDate);

            $periodDebit = (float) (clone $movementQuery)->where('gl.sens', 'debit')->sum('gl.montant');
            $periodCredit = (float) (clone $movementQuery)->where('gl.sens', 'credit')->sum('gl.montant');

            // Cumuls
            $totalDebitAccount = $openingDebit + $periodDebit;
            $totalCreditAccount = $openingCredit + $periodCredit;

            if ($totalDebitAccount >= $totalCreditAccount) {
                $balanceDebit = $totalDebitAccount - $totalCreditAccount;
                $balanceCredit = 0;
            } else {
                $balanceDebit = 0;
                $balanceCredit = $totalCreditAccount - $totalDebitAccount;
            }

            $results[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->libelle,
                'account_class' => $account->classe ?? substr($account->code, 0, 1),
                'account_type' => '',
                'account_nature' => '',
                'opening_debit' => $openingDebit,
                'opening_credit' => $openingCredit,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'total_debit' => $totalDebitAccount,
                'total_credit' => $totalCreditAccount,
                'balance_debit' => $balanceDebit,
                'balance_credit' => $balanceCredit,
            ];

            $totalDebit += $periodDebit;
            $totalCredit += $periodCredit;
            $totalBalanceDebit += $balanceDebit;
            $totalBalanceCredit += $balanceCredit;
        }

        return [
            'parameters' => [
                'client_id' => $clientId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'class_filter' => $classFilter,
            ],
            'accounts' => $results,
            'totals' => [
                'period_debit' => round($totalDebit, 2),
                'period_credit' => round($totalCredit, 2),
                'balance_debit' => round($totalBalanceDebit, 2),
                'balance_credit' => round($totalBalanceCredit, 2),
            ],
        ];
    }

    /**
     * Export CSV de la balance
     */
    public function exportCsv(int $clientId, ?string $startDate = null, ?string $endDate = null): string
    {
        $data = $this->generate($clientId, $startDate, $endDate);

        $csv = "Code compte;Libellé;Débit ouverture;Crédit ouverture;Débit période;Crédit période;Solde débiteur;Solde créditeur\n";

        foreach ($data['accounts'] as $account) {
            $csv .= implode(';', [
                $account['account_code'],
                $account['account_name'],
                number_format($account['opening_debit'], 2, ',', ''),
                number_format($account['opening_credit'], 2, ',', ''),
                number_format($account['period_debit'], 2, ',', ''),
                number_format($account['period_credit'], 2, ',', ''),
                number_format($account['balance_debit'], 2, ',', ''),
                number_format($account['balance_credit'], 2, ',', ''),
            ]) . "\n";
        }

        return $csv;
    }
}
