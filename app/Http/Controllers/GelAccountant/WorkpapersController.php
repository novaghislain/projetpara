<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\FiscalYear;
use App\Models\Workpaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkpapersController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $fiscalYear = FiscalYear::where('client_id', $clientId)
            ->where('is_active', true)
            ->latest()
            ->first();

        $period = $request->get('period', $fiscalYear?->year ?? date('Y'));

        // Tous les comptes du plan comptable avec leur statut workpaper
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($clientId, $fiscalYear, $period) {
                $wp = Workpaper::where('client_id', $clientId)
                    ->where('account_id', $account->id)
                    ->where('period', $period)
                    ->first();
                $account->workpaper = $wp;
                $account->wp_status = $wp?->status ?? 'not_reviewed';
                return $account;
            });

        $total        = $accounts->count();
        $reviewed     = $accounts->where('wp_status', 'reviewed')->count();
        $in_progress  = $accounts->where('wp_status', 'in_progress')->count();
        $progress_pct = $total > 0 ? round(($reviewed / $total) * 100) : 0;

        return view('gel-accountant.workpapers.index', compact(
            'accounts', 'period', 'fiscalYear', 'reviewed', 'in_progress', 'total', 'progress_pct'
        ));
    }

    public function updateStatus(Request $request, $accountId)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

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
