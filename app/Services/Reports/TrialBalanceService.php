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
        $startDate = date('Y-01-01', strtotime($date));

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $lines = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $totals = DB::table('entry_lines')
                ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
                ->where('entry_lines.account_id', $account->id)
                ->where('journal_entries.client_id', $clientId)
                ->where('journal_entries.status', 'posted')
                ->whereDate('journal_entries.entry_date', '<=', $date)
                ->selectRaw('COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
                ->first();

            $debit = (float) $totals->debit;
            $credit = (float) $totals->credit;

            if ($debit == 0 && $credit == 0) continue;

            $lines[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'class' => $account->syscohada_class ?? substr($account->code, 0, 1),
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

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->where('code', 'like', $prefix . '%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $result = [];

        foreach ($accounts as $account) {
            $entries = DB::table('entry_lines')
                ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
                ->where('entry_lines.account_id', $account->id)
                ->where('journal_entries.client_id', $clientId)
                ->where('journal_entries.status', 'posted')
                ->whereDate('journal_entries.entry_date', '<=', $asOfDate)
                ->select('journal_entries.entry_date', 'journal_entries.reference', 'entry_lines.debit', 'entry_lines.credit')
                ->orderBy('journal_entries.entry_date')
                ->get();

            $solde = 0;
            foreach ($entries as $entry) {
                $solde += (float) $entry->debit - (float) $entry->credit;
            }

            if (abs($solde) < 0.01) continue;

            $balance = abs($solde);
            $agingBuckets = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '91_plus' => 0];

            foreach ($entries as $entry) {
                $days = (int) ((strtotime($asOfDate) - strtotime($entry->entry_date)) / 86400);
                $amount = (float) ($type === 'customer' ? $entry->debit : $entry->credit);

                if ($days <= 30) $agingBuckets['0_30'] += $amount;
                elseif ($days <= 60) $agingBuckets['31_60'] += $amount;
                elseif ($days <= 90) $agingBuckets['61_90'] += $amount;
                else $agingBuckets['91_plus'] += $amount;
            }

            $result[] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
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
