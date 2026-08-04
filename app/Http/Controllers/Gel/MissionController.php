<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Client;
use App\Models\Pole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MissionController extends Controller
{
    /**
     * Contrôleur de gestion des missions.
     * Permet de créer, modifier, suivre et lister les missions
     * avec affectation des collaborateurs et suivi de progression.
     */

    /**
     * Page liste des missions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'gel-missions']);
    }

    /**
     * Page formulaire de création d'une mission.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('app', ['page' => 'gel-missions-create']);
    }

    /**
     * Crée une nouvelle mission avec ses collaborateurs.
     *
     * @param Request $request La requête HTTP avec les données de la mission
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'pole_id' => 'required|exists:poles,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:a_faire,en_cours,terminee,annulee',
            'priority' => 'nullable|string|in:basse,moyenne,haute,critique',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'collaborators' => 'nullable|array',
            'collaborators.*' => 'exists:users,id',
        ]);

        // Valeurs par défaut
        $validated['status'] = $validated['status'] ?? 'a_faire';
        $validated['created_by'] = Auth::id();

        $mission = Mission::create($validated);

        // Ajout des collaborateurs à la mission
        if (!empty($validated['collaborators'])) {
            $collaboratorsData = [];
            foreach ($validated['collaborators'] as $userId) {
                $collaboratorsData[$userId] = ['role' => 'collaborator'];
            }
            $mission->collaborators()->attach($collaboratorsData);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Mission créée avec succès', 'mission' => $mission], 201);
        }

        return redirect()->route('missions.show', $mission->id)
            ->with('success', 'Mission créée avec succès');
    }

    /**
     * Page détail d'une mission.
     *
     * @param int $id L'identifiant de la mission
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('app', [
            'page' => 'gel-missions-show',
            'missionId' => $id,
        ]);
    }

    /**
     * Page formulaire d'édition d'une mission.
     *
     * @param int $id L'identifiant de la mission
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('app', [
            'page' => 'gel-missions-edit',
            'missionId' => $id,
        ]);
    }

    /**
     * Met à jour une mission et synchronise ses collaborateurs.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant de la mission
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $mission = Mission::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'pole_id' => 'required|exists:poles,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:a_faire,en_cours,terminee,annulee',
            'priority' => 'nullable|string|in:basse,moyenne,haute,critique',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'collaborators' => 'nullable|array',
            'collaborators.*' => 'exists:users,id',
        ]);

        $mission->update($validated);

        // Synchronisation de la liste des collaborateurs
        if (isset($validated['collaborators'])) {
            $collaboratorsData = [];
            foreach ($validated['collaborators'] as $userId) {
                $collaboratorsData[$userId] = ['role' => 'collaborator'];
            }
            $mission->collaborators()->sync($collaboratorsData);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Mission mise à jour avec succès', 'mission' => $mission]);
        }

        return redirect()->route('missions.show', $mission->id)
            ->with('success', 'Mission mise à jour avec succès');
    }

    /**
     * Supprime une mission.
     *
     * @param int $id L'identifiant de la mission
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $mission = Mission::findOrFail($id);
        $mission->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Mission supprimée avec succès']);
        }

        return redirect()->route('missions.index')
            ->with('success', 'Mission supprimée avec succès');
    }

    /**
     * Met à jour uniquement la progression (pourcentage) d'une mission.
     * Passe automatiquement le statut à "terminee" si progression atteint 100%.
     *
     * @param Request $request La requête HTTP avec le nouveau pourcentage
     * @param int $id L'identifiant de la mission
     * @return \Illuminate\Http\JsonResponse La mission mise à jour
     */
    public function updateProgress(Request $request, $id)
    {
        $mission = Mission::findOrFail($id);

        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'nullable|string|in:a_faire,en_cours,terminee,annulee',
        ]);

        $data = ['progress' => $validated['progress']];
        if (isset($validated['status'])) {
            $data['status'] = $validated['status'];
        }

        // Passage automatique à "terminee" si la progression atteint 100%
        if ($validated['progress'] >= 100 && !isset($validated['status'])) {
            $data['status'] = 'terminee';
        }

        $mission->update($data);

        return response()->json($mission);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API : Liste toutes les missions avec filtrage par rôle.
     *
     * @return \Illuminate\Http\JsonResponse La liste des missions
     */
    public function listAll()
    {
        $user = Auth::user();
        $query = Mission::with([
            'client:id,company_name',
            'pole:id,name',
            'assignedTo:id,name',
            'createdBy:id,name',
        ]);

        // Filtrage selon le rôle : les utilisateurs non-admin voient
        // uniquement les missions de leur pôle ou qui les concernent
        if (!in_array($user->role, ['super_admin', 'director'])) {
            $query->where(function ($q) use ($user) {
                $q->where('pole_id', $user->pole_id)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        return response()->json($query->latest()->get());
    }

    /**
     * API : Détail d'une mission avec toutes ses relations.
     *
     * @param int $id L'identifiant de la mission
     * @return \Illuminate\Http\JsonResponse La mission avec ses relations
     */
    public function getMission($id)
    {
        $mission = Mission::with([
            'client',
            'pole',
            'assignedTo',
            'createdBy',
            'collaborators',
        ])->findOrFail($id);

        return response()->json($mission);
    }
}
