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
        $account = AccountingAccount::where('id', $accountId)
            ->where('client_id', $clientId)
            ->firstOrFail();

        $startDate = $startDate ?? date('Y-01-01');
        $endDate = $endDate ?? date('Y-m-d');

        // Solde antérieur (avant startDate)
        $openingQuery = DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->where('entry_lines.account_id', $accountId)
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '<', $startDate);

        $openingDebit = (float) $openingQuery->sum('entry_lines.debit');
        $openingCredit = (float) $openingQuery->sum('entry_lines.credit');
        $openingBalance = $openingDebit - $openingCredit;

        // Lignes de la période (paginated)
        $linesQuery = DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->leftJoin('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->where('entry_lines.account_id', $accountId)
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '>=', $startDate)
            ->whereDate('journal_entries.entry_date', '<=', $endDate)
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.created_at')
            ->select([
                'journal_entries.id as entry_id',
                'journal_entries.entry_date',
                'journal_entries.reference',
                'journal_entries.description as entry_description',
                'journals.code as journal_code',
                'journals.label as journal_name',
                'entry_lines.id as line_id',
                'entry_lines.debit',
                'entry_lines.credit',
                'entry_lines.description as line_description',
            ]);

        $totalDebit = (float) (clone $linesQuery)->sum('debit');
        $totalCredit = (float) (clone $linesQuery)->sum('credit');

        $perPage = min($perPage, 500);
        $page = request()->input('page', 1);
        $total = $linesQuery->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $items = $linesQuery->offset($offset)->limit($perPage)->get();

        // Calcul du solde courant avec running balance
        $runningBalance = $openingBalance;
        $linesCollection = collect($items)->map(function ($line) use (&$runningBalance) {
            $runningBalance += (float) $line->debit - (float) $line->credit;
            return [
                'entry_id' => $line->entry_id,
                'entry_date' => $line->entry_date,
                'reference' => $line->reference,
                'journal_code' => $line->journal_code,
                'journal_name' => $line->journal_name,
                'entry_description' => $line->entry_description,
                'line_description' => $line->line_description,
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
                'running_balance' => round($runningBalance, 2),
            ];
        });

        $closingBalance = $openingBalance + $totalDebit - $totalCredit;

        return [
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'nature' => $account->account_nature,
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
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->where('code', 'like', $classCode . '%')
            ->where('is_active', true)
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
