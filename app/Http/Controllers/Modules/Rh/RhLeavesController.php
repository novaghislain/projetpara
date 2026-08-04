<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Models\Rh\RhLeaveRequest;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des demandes de congés RH.
 *
 * Permet de créer, consulter, approuver/rejeter et supprimer
 * les demandes de congés et absences des employés.
 */
class RhLeavesController extends BaseRhController
{
    /**
     * Affiche la liste des demandes de congés ou la vue associée.
     *
     * Si la requête attend du JSON, retourne les demandes paginées
     * avec filtrage optionnel par statut et type de congé.
     *
     * @param Request $request La requête HTTP avec les filtres (statut, type)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View Liste paginée des congés ou vue
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
            $query = RhLeaveRequest::whereIn('employee_id', $employeeIds)->with('employee');

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-leaves']);
    }

    /**
     * Crée une nouvelle demande de congé.
     *
     * @param Request $request La requête HTTP contenant les données de la demande
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Demande créée ou redirection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'type' => 'required|string|in:conge,maladie,maternite,paternite,formation,autre',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'duree_jours' => 'nullable|integer|min:1',
            'motif' => 'nullable|string|max:2000',
        ]);

        $validated['statut'] = 'pending';
        $leave = RhLeaveRequest::create($validated);

        if ($request->expectsJson()) {
            return response()->json($leave->load('employee'), 201);
        }
        return redirect()->route('rh.leaves.index')->with('success', 'Demande de congé créée.');
    }

    /**
     * Approuve, rejette ou annule une demande de congé.
     *
     * @param Request $request La requête HTTP contenant le statut et les notes
     * @param mixed $id L'identifiant de la demande de congé
     * @return \Illuminate\Http\JsonResponse La demande mise à jour
     */
    public function approuver(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $leave = RhLeaveRequest::whereIn('employee_id', $employeeIds)->findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|string|in:approved,rejected,cancelled',
            'notes_approbateur' => 'nullable|string|max:2000',
        ]);

        $validated['approbateur_id'] = Auth::id();
        $validated['date_approbation'] = now();
        $leave->update($validated);

        return response()->json($leave->load('employee'));
    }

    /**
     * Supprime une demande de congé.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant de la demande à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Message de confirmation ou redirection
     */
    public function destroy(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $leave = RhLeaveRequest::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $leave->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Demande supprimée.']);
        }
        return redirect()->route('rh.leaves.index')->with('success', 'Demande supprimée.');
    }
}
