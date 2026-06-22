<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\AccountingBudget;
use App\Models\AccountingBudgetLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends BaseGelAccountingController
{
    /**
     * Page liste des budgets.
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-accounting-budgets',
            'clientId' => $clientId,
        ]);
    }

    /**
     * API: Liste des budgets pour un client.
     */
    public function listAll($clientId)
    {
        $budgets = AccountingBudget::where('client_id', $clientId)
            ->with(['fiscalYear', 'createdBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($budgets);
    }

    /**
     * API: Détail d'un budget avec ses lignes.
     */
    public function show($clientId, $id)
    {
        $budget = AccountingBudget::where('client_id', $clientId)
            ->with(['fiscalYear', 'lines.account', 'createdBy:id,name', 'validatedBy:id,name'])
            ->findOrFail($id);

        return response()->json($budget);
    }

    /**
     * API: Créer un budget.
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:recette,depense,tresorerie,investissement',
            'montant_prevu' => 'required|numeric|min:0',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['client_id'] = $clientId;
        $validated['status'] = 'brouillon';
        $validated['created_by'] = Auth::id();

        $budget = AccountingBudget::create($validated);

        return response()->json($budget->load('fiscalYear'), 201);
    }

    /**
     * API: Ajouter une ligne à un budget.
     */
    public function addLine(Request $request, $budgetId)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounting_accounts,id',
            'label' => 'required|string|max:255',
            'montant_prevu' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $budget = AccountingBudget::findOrFail($budgetId);
        $validated['budget_id'] = $budget->id;

        $line = AccountingBudgetLine::create($validated);

        // Mettre à jour le montant prévu du budget
        $budget->increment('montant_prevu', $validated['montant_prevu']);

        return response()->json($line->load('account'), 201);
    }

    /**
     * API: Mettre à jour une ligne de budget.
     */
    public function updateLine(Request $request, $budgetId, $lineId)
    {
        $validated = $request->validate([
            'montant_prevu' => 'nullable|numeric|min:0',
            'label' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $line = AccountingBudgetLine::where('budget_id', $budgetId)->findOrFail($lineId);
        $oldMontant = $line->montant_prevu;
        $line->update($validated);

        // Ajuster le total du budget
        $diff = ($validated['montant_prevu'] ?? $oldMontant) - $oldMontant;
        $line->budget->increment('montant_prevu', $diff);

        return response()->json($line->fresh('account'));
    }

    /**
     * API: Supprimer une ligne de budget.
     */
    public function removeLine($budgetId, $lineId)
    {
        $line = AccountingBudgetLine::where('budget_id', $budgetId)->findOrFail($lineId);
        $line->budget->decrement('montant_prevu', $line->montant_prevu);
        $line->delete();

        return response()->json(['message' => 'Ligne supprimée']);
    }

    /**
     * API: Valider un budget.
     */
    public function valider($clientId, $id)
    {
        $budget = AccountingBudget::where('client_id', $clientId)->findOrFail($id);

        if ($budget->status !== 'brouillon') {
            return response()->json(['message' => 'Le budget a déjà été traité'], 409);
        }

        $budget->update([
            'status' => 'actif',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return response()->json($budget);
    }

    /**
     * API: Verrouiller un budget (plus de modifications).
     */
    public function verrouiller($clientId, $id)
    {
        $budget = AccountingBudget::where('client_id', $clientId)->findOrFail($id);
        $budget->update(['status' => 'verrouille']);

        return response()->json($budget);
    }

    /**
     * API: Supprimer un budget.
     */
    public function destroy($clientId, $id)
    {
        $budget = AccountingBudget::where('client_id', $clientId)->findOrFail($id);

        if ($budget->status === 'verrouille') {
            return response()->json(['message' => 'Un budget verrouillé ne peut pas être supprimé'], 409);
        }

        $budget->lines()->delete();
        $budget->delete();

        return response()->json(['message' => 'Budget supprimé']);
    }
}
