<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Models\Rh\RhExpense;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des notes de frais RH.
 *
 * Permet de créer, consulter, approuver/rejeter et supprimer
 * les notes de frais soumises par les employés.
 */
class RhExpensesController extends BaseRhController
{
    /**
     * Affiche la liste des notes de frais ou la vue associée.
     *
     * Si la requête attend du JSON, retourne les notes de frais paginées
     * avec filtrage optionnel par statut.
     *
     * @param Request $request La requête HTTP avec le filtre (statut)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View Liste paginée des notes de frais ou vue
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
            $query = RhExpense::whereIn('employee_id', $employeeIds)->with('employee');

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-expenses']);
    }

    /**
     * Crée une nouvelle note de frais.
     *
     * @param Request $request La requête HTTP contenant les données de la note de frais
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Note de frais créée ou redirection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'categorie' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'justificatif_url' => 'nullable|string|max:500',
        ]);

        $validated['statut'] = 'pending';
        $expense = RhExpense::create($validated);

        if ($request->expectsJson()) {
            return response()->json($expense->load('employee'), 201);
        }
        return redirect()->route('rh.expenses.index')->with('success', 'Note de frais créée.');
    }

    /**
     * Approuve, rejette ou marque comme payée une note de frais.
     *
     * @param Request $request La requête HTTP contenant le statut et le justificatif
     * @param mixed $id L'identifiant de la note de frais
     * @return \Illuminate\Http\JsonResponse La note de frais mise à jour
     */
    public function approuver(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $expense = RhExpense::whereIn('employee_id', $employeeIds)->findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|string|in:approved,rejected,paid',
            'justificatif_url' => 'nullable|string|max:500',
        ]);

        $validated['approbateur_id'] = Auth::id();
        $validated['date_approbation'] = now();
        if ($request->statut === 'paid') {
            $validated['date_paiement'] = now();
        }
        $expense->update($validated);

        return response()->json($expense->load('employee'));
    }

    /**
     * Supprime une note de frais.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant de la note de frais à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Message de confirmation ou redirection
     */
    public function destroy(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $expense = RhExpense::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $expense->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Note de frais supprimée.']);
        }
        return redirect()->route('rh.expenses.index')->with('success', 'Note de frais supprimée.');
    }
}
