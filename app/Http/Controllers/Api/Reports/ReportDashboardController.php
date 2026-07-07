<?php
namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportDashboardController extends Controller
{
    /**
     * Tableau de bord des rapports comptables
     * Vue d'ensemble rapide des indicateurs financiers
     */
    public function index(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
        $date = $request->input('as_of_date', date('Y-m-d'));
        $startDate = $request->input('start_date', date('Y-01-01'));
        $endDate = $request->input('end_date', $date);

        // 1. Balance de vérification (débits = crédits ?)
        $trialBalance = DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->first();

        $totalDebit = (float) $trialBalance->total_debit;
        $totalCredit = (float) $trialBalance->total_credit;

        // 2. Chiffre d'affaires (classe 7)
        $ca = (float) DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->join('accounting_accounts', 'entry_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_accounts.client_id', $clientId)
            ->where('accounting_accounts.code', 'like', '70%')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '>=', $startDate)
            ->whereDate('journal_entries.entry_date', '<=', $endDate)
            ->sum('entry_lines.credit');

        // 3. Total charges (classe 6)
        $charges = (float) DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->join('accounting_accounts', 'entry_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_accounts.client_id', $clientId)
            ->where('accounting_accounts.code', 'like', '6%')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '>=', $startDate)
            ->whereDate('journal_entries.entry_date', '<=', $endDate)
            ->sum('entry_lines.debit');

        // 4. Trésorerie (classe 5)
        $tresorerie = (float) DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->join('accounting_accounts', 'entry_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_accounts.client_id', $clientId)
            ->where('accounting_accounts.code', 'like', '5%')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(entry_lines.debit), 0) - COALESCE(SUM(entry_lines.credit), 0) as balance')
            ->value('balance');

        // 5. Total créances clients (comptes 41)
        $creances = (float) DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->join('accounting_accounts', 'entry_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_accounts.client_id', $clientId)
            ->where('accounting_accounts.code', 'like', '41%')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(entry_lines.debit), 0) - COALESCE(SUM(entry_lines.credit), 0) as balance')
            ->value('balance');

        // 6. Total dettes fournisseurs (comptes 40)
        $dettes = (float) DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->join('accounting_accounts', 'entry_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_accounts.client_id', $clientId)
            ->where('accounting_accounts.code', 'like', '40%')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(entry_lines.credit), 0) - COALESCE(SUM(entry_lines.debit), 0) as balance')
            ->value('balance');

        // 7. Nombre d'écritures / mois (pour le graphique)
        $monthlyEntries = DB::table('journal_entries')
            ->where('client_id', $clientId)
            ->where('status', 'posted')
            ->whereYear('entry_date', date('Y', strtotime($endDate)))
            ->selectRaw("strftime('%m', entry_date) as month, COUNT(*) as count, SUM(total_debit) as total")
            ->groupBy(DB::raw("strftime('%m', entry_date)"))
            ->orderBy('month')
            ->get();

        // 8. Comptes les plus actifs
        $topAccounts = DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->join('accounting_accounts', 'entry_lines.account_id', '=', 'accounting_accounts.id')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '>=', $startDate)
            ->whereDate('journal_entries.entry_date', '<=', $endDate)
            ->groupBy('accounting_accounts.id', 'accounting_accounts.code', 'accounting_accounts.name')
            ->select([
                'accounting_accounts.code',
                'accounting_accounts.name',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('ROUND(SUM(entry_lines.debit) + SUM(entry_lines.credit), 2) as total_movement'),
            ])
            ->orderByDesc('total_movement')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'parameters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'verification' => [
                    'total_debit' => round($totalDebit, 2),
                    'total_credit' => round($totalCredit, 2),
                    'difference' => round($totalDebit - $totalCredit, 2),
                    'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
                ],
                'indicators' => [
                    'chiffre_affaires' => round($ca, 2),
                    'total_charges' => round($charges, 2),
                    'resultat_brut' => round($ca - $charges, 2),
                    'tresorerie' => round($tresorerie ?? 0, 2),
                    'creances_clients' => round($creances ?? 0, 2),
                    'dettes_fournisseurs' => round($dettes ?? 0, 2),
                ],
                'monthly_entries' => $monthlyEntries,
                'top_accounts' => $topAccounts,
            ],
        ]);
    }
}
