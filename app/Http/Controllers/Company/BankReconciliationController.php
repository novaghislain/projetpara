<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AccountingJournal;
use App\Models\BankReconciliation;
use App\Models\FiscalYear;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    private function getClientId()
    {
        $user = Auth::user();
        if (!$user->client_id) abort(403, 'Aucune entreprise associée.');
        return $user->client_id;
    }

    /**
     * Liste des réconciliations.
     */
    public function index()
    {
        $clientId = $this->getClientId();

        $reconciliations = BankReconciliation::where('client_id', $clientId)
            ->with('fiscalYear')
            ->orderBy('period', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id'                   => $r->id,
                    'bank_account'         => $r->bank_account,
                    'bank_name'            => $r->bank_name,
                    'period'               => $r->period,
                    'statement_date'       => $r->statement_date?->format('Y-m-d'),
                    'balance_per_statement'=> (float) $r->balance_per_statement,
                    'balance_per_books'    => (float) $r->balance_per_books,
                    'difference'           => (float) $r->difference,
                    'status'               => $r->status,
                ];
            });

        return response()->json($reconciliations);
    }

    /**
     * Crée une réconciliation.
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'bank_account'         => 'required|string|max:50',
            'bank_name'            => 'nullable|string|max:255',
            'period'               => 'required|regex:/^\d{4}-\d{2}$/',
            'statement_date'       => 'required|date',
            'balance_per_statement'=> 'required|numeric',
            'balance_per_books'    => 'required|numeric',
            'fiscal_year_id'       => 'nullable|exists:fiscal_years,id',
        ]);

        $difference = $validated['balance_per_statement'] - $validated['balance_per_books'];

        $reconciliation = BankReconciliation::create([
            'client_id'            => $clientId,
            'fiscal_year_id'       => $validated['fiscal_year_id'] ?? null,
            'bank_account'         => $validated['bank_account'],
            'bank_name'            => $validated['bank_name'] ?? null,
            'period'               => $validated['period'],
            'statement_date'       => $validated['statement_date'],
            'balance_per_statement'=> $validated['balance_per_statement'],
            'balance_per_books'    => $validated['balance_per_books'],
            'difference'           => round($difference, 2),
            'status'               => 'draft',
        ]);

        return response()->json([
            'message'        => 'Réconciliation créée.',
            'reconciliation' => $reconciliation,
        ], 201);
    }

    /**
     * Affiche une réconciliation.
     */
    public function show($id)
    {
        $clientId = $this->getClientId();
        $r = BankReconciliation::where('client_id', $clientId)->with('fiscalYear')->findOrFail($id);
        return response()->json(['reconciliation' => $r]);
    }

    /**
     * Met à jour les montants de rapprochement.
     */
    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $r = BankReconciliation::where('client_id', $clientId)->where('status', 'draft')->findOrFail($id);

        $validated = $request->validate([
            'outstanding_deposits' => 'nullable|numeric|min:0',
            'outstanding_checks'   => 'nullable|numeric|min:0',
            'bank_charges'         => 'nullable|numeric|min:0',
            'interest_income'      => 'nullable|numeric|min:0',
            'unmatched_items'      => 'nullable|array',
        ]);

        $r->update($validated);

        return response()->json([
            'message'        => 'Réconciliation mise à jour.',
            'reconciliation' => $r->fresh(),
        ]);
    }

    /**
     * Marque comme rapprochée.
     */
    public function match($id)
    {
        $clientId = $this->getClientId();
        $r = BankReconciliation::where('client_id', $clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $r->update(['status' => 'matched']);

        AuditTrailService::log($r, 'matched', null, $r->toArray(), 'Réconciliation bancaire effectuée');

        return response()->json([
            'message'        => 'Réconciliation effectuée.',
            'reconciliation' => $r->fresh(),
        ]);
    }

    /**
     * Approuve la réconciliation.
     */
    public function approve($id)
    {
        $clientId = $this->getClientId();
        $r = BankReconciliation::where('client_id', $clientId)
            ->where('status', 'matched')
            ->findOrFail($id);

        $r->update(['status' => 'approved']);

        return response()->json([
            'message'        => 'Réconciliation approuvée.',
            'reconciliation' => $r->fresh(),
        ]);
    }

    /**
     * Supprime une réconciliation.
     */
    public function destroy($id)
    {
        $clientId = $this->getClientId();
        $r = BankReconciliation::where('client_id', $clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $r->delete();
        return response()->json(['message' => 'Réconciliation supprimée.']);
    }
}
