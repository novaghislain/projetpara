<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des employés RH.
 *
 * Permet d'effectuer les opérations CRUD sur les employés :
 * consultation, création, modification, suppression et affichage
 * des détails avec les relations associées.
 */
class RhEmployeesController extends BaseRhController
{
    /**
     * Affiche la liste des employés ou la vue associée.
     *
     * Si la requête attend du JSON, retourne les employés paginés
     * avec filtrage optionnel par statut et recherche textuelle.
     *
     * @param Request $request La requête HTTP avec les filtres (status, search)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View Liste paginée des employés ou vue
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = RhEmployee::byClient($this->getClientId($request))->with(['contracts']);
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('nom', 'like', "%{$s}%")
                      ->orWhere('prenom', 'like', "%{$s}%")
                      ->orWhere('matricule', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                      ->orWhere('poste', 'like', "%{$s}%");
                });
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-employees']);
    }

    /**
     * Affiche le formulaire de création d'un employé.
     *
     * @param Request $request La requête HTTP
     * @return \Illuminate\View\View La vue du formulaire de création
     */
    public function create(Request $request)
    {
        return view('app', ['page' => 'rh-employees-create']);
    }

    /**
     * Enregistre un nouvel employé.
     *
     * @param Request $request La requête HTTP contenant les données de l'employé
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Employé créé ou redirection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule' => 'nullable|string|max:50',
            'civilite' => 'nullable|string|in:M.,Mme,Mlle',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'situation_matrimoniale' => 'nullable|string|in:celibataire,marié(e),divorcé(e),veuf(ve)',
            'nombre_enfants' => 'nullable|integer|min:0',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'date_embauche' => 'nullable|date',
            'date_depart' => 'nullable|date',
            'type_contrat' => 'nullable|string|in:CDI,CDD,INTERIM,STAGE,PRESTATION',
            'salaire_base' => 'nullable|numeric|min:0',
            'cnss_number' => 'nullable|string|max:50',
            'ifu_number' => 'nullable|string|max:50',
            'banque' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:50',
            'urgence_nom' => 'nullable|string|max:255',
            'urgence_phone' => 'nullable|string|max:50',
            'photo' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:actif,suspendu,quitte',
        ]);

        $validated['client_id'] = $this->getClientId($request);
        $validated['created_by'] = Auth::id();

        $employee = RhEmployee::create($validated);

        if ($request->expectsJson()) {
            return response()->json($employee->load('contracts'), 201);
        }
        return redirect()->route('rh.employees.index')->with('success', 'Employé créé avec succès.');
    }

    /**
     * Affiche les détails d'un employé avec ses relations.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant de l'employé
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View Détails de l'employé ou vue
     */
    public function show(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId($request))
            ->with(['contracts', 'leaveRequests', 'expenses', 'payrolls', 'attendance', 'trainings'])
            ->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json($employee);
        }
        return view('app', ['page' => 'rh-employees-show', 'id' => $id]);
    }

    /**
     * Affiche le formulaire de modification d'un employé.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant de l'employé à modifier
     * @return \Illuminate\View\View La vue du formulaire d'édition
     */
    public function edit(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId($request))->findOrFail($id);
        return view('app', ['page' => 'rh-employees-edit', 'id' => $id]);
    }

    /**
     * Met à jour les informations d'un employé.
     *
     * @param Request $request La requête HTTP contenant les données à mettre à jour
     * @param mixed $id L'identifiant de l'employé
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Employé mis à jour ou redirection
     */
    public function update(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId($request))->findOrFail($id);

        $validated = $request->validate([
            'matricule' => 'nullable|string|max:50',
            'civilite' => 'nullable|string|in:M.,Mme,Mlle',
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'sometimes|required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'situation_matrimoniale' => 'nullable|string|in:celibataire,marié(e),divorcé(e),veuf(ve)',
            'nombre_enfants' => 'nullable|integer|min:0',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'date_embauche' => 'nullable|date',
            'date_depart' => 'nullable|date',
            'type_contrat' => 'nullable|string|in:CDI,CDD,INTERIM,STAGE,PRESTATION',
            'salaire_base' => 'nullable|numeric|min:0',
            'cnss_number' => 'nullable|string|max:50',
            'ifu_number' => 'nullable|string|max:50',
            'banque' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:50',
            'urgence_nom' => 'nullable|string|max:255',
            'urgence_phone' => 'nullable|string|max:50',
            'photo' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:actif,suspendu,quitte',
        ]);

        $employee->update($validated);

        if ($request->expectsJson()) {
            return response()->json($employee->load('contracts'));
        }
        return redirect()->route('rh.employees.show', $id)->with('success', 'Employé mis à jour.');
    }

    /**
     * Supprime un employé.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant de l'employé à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Message de confirmation ou redirection
     */
    public function destroy(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId($request))->findOrFail($id);
        $employee->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Employé supprimé.']);
        }
        return redirect()->route('rh.employees.index')->with('success', 'Employé supprimé.');
    }
}
