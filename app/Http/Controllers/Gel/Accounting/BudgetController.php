<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\AccountingBudget;
use App\Models\AccountingBudgetLine;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends BaseGelAccountingController
{
    /**
     * Contrôleur de gestion des budgets.
     * Permet de créer, modifier, valider et verrouiller des budgets
     * avec leurs lignes budgétaires par exercice fiscal et par client.
     */

    /**
     * Page liste des budgets.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\View\View
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-accounting-budgets',
            'clientId' => $clientId,
        ]);
    }

    /**
     * API : Liste des exercices fiscaux pour le sélecteur du formulaire budget.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse La liste des exercices fiscaux
     */
    public function fiscalYears($clientId)
    {
        $years = FiscalYear::where('client_id', $clientId)
            ->orderBy('year', 'desc')
            ->get(['id', 'year', 'date_start', 'date_end', 'status']);

        return response()->json($years);
    }

    /**
     * API : Liste des budgets pour un client.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse La liste des budgets
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
     * API : Détail d'un budget avec ses lignes.
     *
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant du budget
     * @return \Illuminate\Http\JsonResponse Le budget avec ses relations
     */
    public function show($clientId, $id)
    {
        $budget = AccountingBudget::where('client_id', $clientId)
            ->with(['fiscalYear', 'lines.account', 'createdBy:id,name', 'validatedBy:id,name'])
            ->findOrFail($id);

        return response()->json($budget);
    }

    /**
     * API : Crée un nouveau budget.
     *
     * @param Request $request La requête HTTP avec les données du budget
     * @return \Illuminate\Http\JsonResponse Le budget créé
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
        $validated['status'] = 'brouillon';       // Statut initial : brouillon
        $validated['created_by'] = Auth::id();    // Utilisateur connecté

        $budget = AccountingBudget::create($validated);

        return response()->json($budget->load('fiscalYear'), 201);
    }

    /**
     * API : Ajoute une ligne à un budget.
     *
     * @param Request $request La requête HTTP avec les données de la ligne
     * @param int $budgetId L'identifiant du budget
     * @return \Illuminate\Http\JsonResponse La ligne créée
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

        // Mise à jour du montant prévu global du budget
        $budget->increment('montant_prevu', $validated['montant_prevu']);

        return response()->json($line->load('account'), 201);
    }

    /**
     * API : Met à jour une ligne de budget.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $budgetId L'identifiant du budget
     * @param int $lineId L'identifiant de la ligne
     * @return \Illuminate\Http\JsonResponse La ligne mise à jour
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

        // Ajustement du total du budget en fonction de la différence
        $diff = ($validated['montant_prevu'] ?? $oldMontant) - $oldMontant;
        $line->budget->increment('montant_prevu', $diff);

        return response()->json($line->fresh('account'));
    }

    /**
     * API : Supprime une ligne de budget.
     *
     * @param int $budgetId L'identifiant du budget
     * @param int $lineId L'identifiant de la ligne
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function removeLine($budgetId, $lineId)
    {
        $line = AccountingBudgetLine::where('budget_id', $budgetId)->findOrFail($lineId);
        $line->budget->decrement('montant_prevu', $line->montant_prevu);
        $line->delete();

        return response()->json(['message' => 'Ligne supprimée']);
    }

    /**
     * API : Valide un budget (passe de brouillon à actif).
     *
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant du budget
     * @return \Illuminate\Http\JsonResponse Le budget validé
     */
    public function valider($clientId, $id)
    {
        $budget = AccountingBudget::where('client_id', $clientId)->findOrFail($id);

        // Un budget déjà traité ne peut pas être re-validé
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
     * API : Verrouille un budget (plus de modifications possibles).
     *
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant du budget
     * @return \Illuminate\Http\JsonResponse Le budget verrouillé
     */
    public function verrouiller($clientId, $id)
    {
        $budget = AccountingBudget::where('client_id', $clientId)->findOrFail($id);
        $budget->update(['status' => 'verrouille']);

        return response()->json($budget);
    }

    /**
     * API : Supprime un budget (sauf s'il est verrouillé).
     *
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant du budget
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function destroy($clientId, $id)
    {
        $budget = AccountingBudget::where('client_id', $clientId)->findOrFail($id);

        // Un budget verrouillé ne peut pas être supprimé
        if ($budget->status === 'verrouille') {
            return response()->json(['message' => 'Un budget verrouillé ne peut pas être supprimé'], 409);
        }

        $budget->lines()->delete();
        $budget->delete();

        return response()->json(['message' => 'Budget supprimé']);
    }
}
