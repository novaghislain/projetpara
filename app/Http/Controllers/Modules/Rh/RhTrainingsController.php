<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Models\Rh\RhTraining;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des formations RH.
 *
 * Permet de planifier, mettre à jour et supprimer les formations
 * des employés avec suivi du statut et des certifications.
 */
class RhTrainingsController extends BaseRhController
{
    /**
     * Affiche la liste des formations ou la vue associée.
     *
     * Si la requête attend du JSON, retourne les formations paginées
     * avec filtrage optionnel par statut.
     *
     * @param Request $request La requête HTTP avec le filtre (statut)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View Liste paginée des formations ou vue
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
            $query = RhTraining::whereIn('employee_id', $employeeIds)->with('employee');

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-trainings']);
    }

    /**
     * Planifie une nouvelle formation.
     *
     * @param Request $request La requête HTTP contenant les données de la formation
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Formation créée ou redirection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'titre' => 'required|string|max:255',
            'organisme' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'duree_heures' => 'nullable|numeric|min:0',
            'cout' => 'nullable|numeric|min:0',
            'type' => 'nullable|string|in:interne,externe,en_ligne',
            'certificat_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['statut'] = 'planifie';
        $training = RhTraining::create($validated);

        if ($request->expectsJson()) {
            return response()->json($training->load('employee'), 201);
        }
        return redirect()->route('rh.trainings.index')->with('success', 'Formation créée.');
    }

    /**
     * Met à jour une formation existante.
     *
     * @param Request $request La requête HTTP contenant les données à mettre à jour
     * @param mixed $id L'identifiant de la formation
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Formation mise à jour ou redirection
     */
    public function update(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $training = RhTraining::whereIn('employee_id', $employeeIds)->findOrFail($id);

        $validated = $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'organisme' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'duree_heures' => 'nullable|numeric|min:0',
            'cout' => 'nullable|numeric|min:0',
            'type' => 'nullable|string|in:interne,externe,en_ligne',
            'statut' => 'nullable|string|in:planifie,en_cours,termine,annule',
            'certificat_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $training->update($validated);

        if ($request->expectsJson()) {
            return response()->json($training->load('employee'));
        }
        return redirect()->route('rh.trainings.index')->with('success', 'Formation mise à jour.');
    }

    /**
     * Supprime une formation.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant de la formation à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Message de confirmation ou redirection
     */
    public function destroy(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $training = RhTraining::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $training->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Formation supprimée.']);
        }
        return redirect()->route('rh.trainings.index')->with('success', 'Formation supprimée.');
    }
}
