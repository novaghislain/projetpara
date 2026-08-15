<?php
namespace App\Services\Reports;

use App\Models\AccountingAccount;
use Illuminate\Support\Facades\DB;

class GeneralLedgerService
{
    /**
     * Grand livre d'un compte
     */
    public function getLedger(
        int $clientId,
        int $accountId,
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 50
    ): array {
        $account = DB::table('gel_account_types')
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            throw new \Exception("Account not found");
        }

        $startDate = $startDate ?? date('Y-01-01');
        $endDate = $endDate ?? date('Y-m-d');

        // Solde antérieur (avant startDate)
        $openingQuery = DB::table('gel_lignes_ecriture as gl')
            ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
            ->where('gl.compte_id', $accountId)
            ->where('ge.client_id', $clientId)
            ->where('ge.valide', true)
            ->whereDate('ge.date_ecriture', '<', $startDate);

        $openingDebit = (float) (clone $openingQuery)->where('gl.sens', 'debit')->sum('gl.montant');
        $openingCredit = (float) (clone $openingQuery)->where('gl.sens', 'credit')->sum('gl.montant');
        $openingBalance = $openingDebit - $openingCredit;

        // Lignes de la période (paginated)
        $linesQuery = DB::table('gel_lignes_ecriture as gl')
            ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
            ->leftJoin('gel_journaux as gj', 'ge.journal_id', '=', 'gj.id')
            ->where('gl.compte_id', $accountId)
            ->where('ge.client_id', $clientId)
            ->where('ge.valide', true)
            ->whereDate('ge.date_ecriture', '>=', $startDate)
            ->whereDate('ge.date_ecriture', '<=', $endDate)
            ->orderBy('ge.date_ecriture')
            ->orderBy('ge.created_at')
            ->select([
                'ge.id as entry_id',
                'ge.date_ecriture as entry_date',
                'ge.reference',
                'ge.libelle as entry_description',
                'gj.code as journal_code',
                'gj.libelle as journal_name',
                'gl.id as line_id',
                'gl.sens',
                'gl.montant',
                'gl.libelle_ligne as line_description',
            ]);

        $totalDebit = (float) (clone $linesQuery)->where('gl.sens', 'debit')->sum('gl.montant');
        $totalCredit = (float) (clone $linesQuery)->where('gl.sens', 'credit')->sum('gl.montant');

        $perPage = min($perPage, 500);
        $page = request()->input('page', 1);
        $total = $linesQuery->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $items = $linesQuery->offset($offset)->limit($perPage)->get();

        $runningBalance = $openingBalance;
        $linesCollection = collect($items)->map(function ($line) use (&$runningBalance) {
            $debit = $line->sens === 'debit' ? (float) $line->montant : 0;
            $credit = $line->sens === 'credit' ? (float) $line->montant : 0;
            $runningBalance += $debit - $credit;
            return [
                'entry_id' => $line->entry_id,
                'entry_date' => $line->entry_date,
                'reference' => $line->reference,
                'journal_code' => $line->journal_code,
                'journal_name' => $line->journal_name,
                'entry_description' => $line->entry_description,
                'line_description' => $line->line_description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => round($runningBalance, 2),
            ];
        });

        $closingBalance = $openingBalance + $totalDebit - $totalCredit;

        return [
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->libelle,
                'type' => $account->classe ?? '',
                'nature' => '',
            ],
            'parameters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($closingBalance, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'lines' => $linesCollection,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    /**
     * Grand livre pour tous les comptes d'une classe
     */
    public function getClassLedger(
        int $clientId,
        string $classCode,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $accounts = DB::table('gel_account_types')
            ->where('code', 'like', $classCode . '%')
            ->orderBy('code')
            ->get();

        $result = [];
        foreach ($accounts as $account) {
            $ledger = $this->getLedger($clientId, $account->id, $startDate, $endDate, 99999);
            if ($ledger['total_debit'] > 0 || $ledger['total_credit'] > 0 || $ledger['opening_balance'] != 0) {
                $result[] = $ledger;
            }
        }

        return $result;
    }
}
