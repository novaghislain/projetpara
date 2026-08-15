<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\FiscalYear;
use App\Models\Workpaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkpapersController extends Controller
{
    public function index(Request $request)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $fiscalYear = FiscalYear::where('client_id', $clientId)
            ->where('is_active', true)
            ->latest()
            ->first();

        $period = $request->get('period', $fiscalYear?->year ?? date('Y'));
        $prevPeriod = $period - 1;

        // Soldes par compte pour la comparaison N vs N-1 (2 colonnes — CDC §4.26)
        $balanceN  = $this->balancesByAccount($clientId, $period);
        $balanceN1 = $this->balancesByAccount($clientId, $prevPeriod);

        // Tous les comptes du plan comptable avec leur statut workpaper
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($clientId, $fiscalYear, $period, $balanceN, $balanceN1) {
                $wp = Workpaper::where('client_id', $clientId)
                    ->where('account_id', $account->id)
                    ->where('period', $period)
                    ->first();
                $account->workpaper = $wp;
                $account->wp_status = $wp?->status ?? 'not_reviewed';
                $account->balance_n  = $balanceN[$account->id] ?? 0.0;
                $account->balance_n1 = $balanceN1[$account->id] ?? 0.0;
                $account->variation  = $account->balance_n - $account->balance_n1;
                return $account;
            });

        $total        = $accounts->count();
        $reviewed     = $accounts->where('wp_status', 'reviewed')->count();
        $in_progress  = $accounts->where('wp_status', 'in_progress')->count();
        $progress_pct = $total > 0 ? round(($reviewed / $total) * 100) : 0;

        return view('gel-accountant.workpapers.index', compact(
            'accounts', 'period', 'prevPeriod', 'fiscalYear', 'reviewed', 'in_progress', 'total', 'progress_pct'
        ));
    }

    /**
     * Soldes (débit − crédit) de chaque compte sur une année donnée.
     * Reprend la logique de la balance de vérification (écritures postées).
     *
     * @return array<int, float> account_id => solde
     */
    private function balancesByAccount(int $clientId, int $year): array
    {
        return DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$year . '-01-01', $year . '-12-31'])
            ->selectRaw('entry_lines.account_id, COALESCE(SUM(entry_lines.debit) - SUM(entry_lines.credit), 0) as solde')
            ->groupBy('entry_lines.account_id')
            ->pluck('solde', 'account_id')
            ->map(fn ($v) => round((float) $v, 2))
            ->toArray();
    }

    public function updateStatus(Request $request, $accountId)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $validated = $request->validate([
            'status' => 'required|in:not_reviewed,in_progress,reviewed',
            'period' => 'required|string',
            'notes'  => 'nullable|string|max:2000',
        ]);

        $wp = Workpaper::updateOrCreate(
            [
                'client_id'  => $clientId,
                'account_id' => $accountId,
                'period'     => $validated['period'],
            ],
            [
                'status'      => $validated['status'],
                'notes'       => $validated['notes'] ?? null,
                'reviewer_id' => $user->id,
                'reviewed_at' => $validated['status'] === 'reviewed' ? now() : null,
            ]
        );

        return response()->json([
            'success' => true,
            'status'  => $wp->status,
            'message' => 'Statut mis à jour.',
        ]);
    }
}
