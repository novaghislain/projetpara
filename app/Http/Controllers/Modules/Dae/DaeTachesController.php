<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeTache;
use Illuminate\Http\Request;

class DaeTachesController extends BaseDaeController
{
    public function index(Request $request)
    {
        $query = DaeTache::with(['client', 'assignedTo', 'sousTaches'])->orderBy('created_at', 'desc');

        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('priorite')) $query->where('priorite', $request->priorite);
        $query->where('client_id', $this->getClientId($request));
        if ($request->filled('assigned_to')) $query->where('assigned_to', $request->assigned_to);
        if ($request->filled('recherche')) {
            $s = $request->recherche;
            $query->where(function ($q) use ($s) {
                $q->where('titre', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if ($request->boolean('urgentes')) {
            $query->whereIn('priorite', ['haute', 'critique'])
                   ->whereIn('statut', ['a_faire', 'en_cours']);
        }

        $taches = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($taches);
        return view('app', ['page' => 'dae-taches']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'       => 'required|string|max:500',
            'description' => 'nullable|string',
            'priorite'    => 'nullable|in:basse,moyenne,haute,critique',
            'statut'      => 'nullable|in:a_faire,en_cours,en_revision,terminee,annulee',
            'echeance'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'parent_id'   => 'nullable|exists:dae_taches,id',
            'tags'        => 'nullable|json',
        ]);

        $validated['priorite'] ??= 'moyenne';
        $validated['statut'] ??= 'a_faire';
        $validated['client_id'] = $this->getClientId($request);

        $tache = DaeTache::create($validated);

        if ($request->expectsJson()) return response()->json($tache, 201);
        return redirect()->route('dae.taches.index')->with('success', 'Tâche créée.');
    }

    public function update(Request $request, $id)
    {
        $tache = DaeTache::findOrFail($id);

        $validated = $request->validate([
            'titre'       => 'sometimes|string|max:500',
            'description' => 'nullable|string',
            'priorite'    => 'sometimes|in:basse,moyenne,haute,critique',
            'statut'      => 'sometimes|in:a_faire,en_cours,en_revision,terminee,annulee',
            'echeance'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'tags'        => 'nullable|json',
        ]);

        if (in_array($validated['statut'] ?? '', ['terminee', 'annulee'])) {
            $validated['completed_at'] = now();
        }

        $tache->update($validated);

        if ($request->expectsJson()) return response()->json($tache);
        return redirect()->route('dae.taches.index')->with('success', 'Tâche mise à jour.');
    }

    public function destroy($id)
    {
        $tache = DaeTache::findOrFail($id);
        $tache->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Tâche supprimée.']);
        return redirect()->route('dae.taches.index')->with('success', 'Tâche supprimée.');
    }

    public function kanban(Request $request)
    {
        $query = DaeTache::with(['client', 'assignedTo'])->where('client_id', $this->getClientId($request));

        $taches = $query->get();

        $columns = [
            'a_faire'    => ['title' => 'À faire', 'tasks' => []],
            'en_cours'   => ['title' => 'En cours', 'tasks' => []],
            'en_revision' => ['title' => 'En révision', 'tasks' => []],
            'terminee'   => ['title' => 'Terminée', 'tasks' => []],
            'annulee'    => ['title' => 'Annulée', 'tasks' => []],
        ];

        foreach ($taches as $tache) {
            $col = $tache->statut;
            if (isset($columns[$col])) {
                $columns[$col]['tasks'][] = $tache;
            }
        }

        if ($request->expectsJson()) return response()->json($columns);
        return redirect()->route('dae.taches.index');
    }

    public function changerStatut(Request $request, $id)
    {
        $request->validate(['statut' => 'required|in:a_faire,en_cours,en_revision,terminee,annulee']);

        $tache = DaeTache::findOrFail($id);
        $update = ['statut' => $request->statut];

        if (in_array($request->statut, ['terminee', 'annulee'])) {
            $update['completed_at'] = now();
        } elseif ($request->statut === 'en_cours') {
            $update['completed_at'] = null;
        }

        $tache->update($update);

        if ($request->expectsJson()) return response()->json($tache);
        return redirect()->back()->with('success', 'Statut mis à jour.');
    }

    public function assigner(Request $request, $id)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $tache = DaeTache::findOrFail($id);
        $tache->update(['assigned_to' => $request->assigned_to]);

        if ($request->expectsJson()) return response()->json($tache);
        return redirect()->back()->with('success', 'Tâche assignée.');
    }
}
