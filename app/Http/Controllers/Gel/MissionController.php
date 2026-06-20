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
     * Page liste des missions.
     */
    public function index()
    {
        return view('app', ['page' => 'gel-missions']);
    }

    /**
     * Page formulaire de création.
     */
    public function create()
    {
        return view('app', ['page' => 'gel-missions-create']);
    }

    /**
     * Créer une mission.
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

        $validated['status'] = $validated['status'] ?? 'a_faire';
        $validated['created_by'] = Auth::id();

        $mission = Mission::create($validated);

        // Ajouter les collaborateurs
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
     */
    public function show($id)
    {
        return view('app', [
            'page' => 'gel-missions-show',
            'missionId' => $id,
        ]);
    }

    /**
     * Page formulaire d'édition.
     */
    public function edit($id)
    {
        return view('app', [
            'page' => 'gel-missions-edit',
            'missionId' => $id,
        ]);
    }

    /**
     * Mettre à jour une mission.
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

        // Synchroniser les collaborateurs
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
     * Supprimer une mission.
     */
    public function destroy($id)
    {
        $client = Mission::findOrFail($id);
        $client->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Mission supprimée avec succès']);
        }

        return redirect()->route('missions.index')
            ->with('success', 'Mission supprimée avec succès');
    }

    /**
     * Mettre à jour uniquement la progression.
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

        // Si progression à 100%, passer automatiquement à "terminee"
        if ($validated['progress'] >= 100 && !isset($validated['status'])) {
            $data['status'] = 'terminee';
        }

        $mission->update($data);

        return response()->json($mission);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API: Liste de toutes les missions.
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
     * API: Détail d'une mission.
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
