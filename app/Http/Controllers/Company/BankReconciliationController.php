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

/**
 * Contrôleur de réconciliation bancaire (Company).
 *
 * Gère le rapprochement des relevés bancaires avec la comptabilité :
 * création, mise à jour, statuts (brouillon → rapproché → approuvé).
 *
 * Chaque réconciliation est liée à un compte bancaire, une période,
 * et compare le solde relevé au solde comptable.
 */
class BankReconciliationController extends BaseCompanyController
{
    /**
     * Liste des réconciliations bancaires du client.
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Crée une réconciliation bancaire.
     *
     * Calcule la différence entre le solde relevé et le solde comptable.
     *
     * @param Request $request Requête HTTP avec les données de réconciliation
     * @return \Illuminate\Http\JsonResponse
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
     * Affiche une réconciliation bancaire (détail).
     *
     * @param int $id Identifiant de la réconciliation
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $clientId = $this->getClientId();
        $r = BankReconciliation::where('client_id', $clientId)->with('fiscalYear')->findOrFail($id);
        return response()->json(['reconciliation' => $r]);
    }

    /**
     * Met à jour les montants de rapprochement (dépôts en circulation,
     * chèques impayés, frais bancaires, intérêts).
     *
     * @param Request $request Requête HTTP avec les montants
     * @param int $id Identifiant de la réconciliation
     * @return \Illuminate\Http\JsonResponse
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
     * Marque la réconciliation comme rapprochée (statut "matched").
     *
     * Enregistre l'action dans la piste d'audit.
     *
     * @param int $id Identifiant de la réconciliation
     * @return \Illuminate\Http\JsonResponse
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
     * Approuve la réconciliation (statut "approved").
     *
     * @param int $id Identifiant de la réconciliation
     * @return \Illuminate\Http\JsonResponse
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
     * Supprime une réconciliation (statut brouillon uniquement).
     *
     * @param int $id Identifiant de la réconciliation
     * @return \Illuminate\Http\JsonResponse
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
