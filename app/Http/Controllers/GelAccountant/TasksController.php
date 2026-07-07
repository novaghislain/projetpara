<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = Task::where('cabinet_id', $cabinetId);

        // Filtre statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre priorité
        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        $tasks = $query->with(['assigne:id,name', 'client:id,nom_entreprise'])
            ->orderBy('date_echeance')
            ->paginate(20);

        $stats = [
            'a_faire' => Task::where('cabinet_id', $cabinetId)->where('statut', 'a_faire')->count(),
            'en_cours' => Task::where('cabinet_id', $cabinetId)->where('statut', 'en_cours')->count(),
            'terminees' => Task::where('cabinet_id', $cabinetId)->where('statut', 'terminee')->count(),
            'echues' => Task::where('cabinet_id', $cabinetId)
                ->whereIn('statut', ['a_faire', 'en_cours'])
                ->where('date_echeance', '<', now())
                ->count(),
        ];

        return view('gel-accountant.tasks.index', compact('tasks', 'stats'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:gel_clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priorite' => 'required|in:basse,moyenne,haute,critique',
            'date_echeance' => 'nullable|date',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['created_by'] = $user->id;
        $validated['statut'] = 'a_faire';

        Task::create($validated);

        return redirect()->route('gel-accountant.tasks')
            ->with('success', 'Tâche créée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priorite' => 'required|in:basse,moyenne,haute,critique',
            'statut' => 'required|in:a_faire,en_cours,terminee',
            'date_echeance' => 'nullable|date',
        ]);

        if ($validated['statut'] === 'terminee') {
            $validated['termine_at'] = now();
        }

        $task->update($validated);

        return redirect()->route('gel-accountant.tasks')
            ->with('success', 'Tâche mise à jour.');
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()->route('gel-accountant.tasks')
            ->with('success', 'Tâche supprimée.');
    }

    public function toggleStatus($id)
    {
        $task = Task::findOrFail($id);
        $task->statut = match ($task->statut) {
            'a_faire' => 'en_cours',
            'en_cours' => 'terminee',
            default => 'a_faire',
        };
        if ($task->statut === 'terminee') {
            $task->termine_at = now();
        }
        $task->save();

        return redirect()->route('gel-accountant.tasks')
            ->with('success', 'Statut de la tâche mis à jour.');
    }
}
