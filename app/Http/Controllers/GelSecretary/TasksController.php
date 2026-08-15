<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dae\DaeTache;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    /**
     * Récupère l'ID du client (entreprise) géré par le secrétaire
     */
    protected function getClientId(Request $request)
    {
        $user = Auth::user();
        return $request->query('client_id') ?? $request->input('client_id') ?? session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
    }

    public function index(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return redirect()->route('gel-secretary.dashboard')
                ->with('error', 'Veuillez sélectionner un client.');
        }

        $tasks = DaeTache::where('client_id', $clientId)
            ->whereNull('parent_id') // On récupère les tâches principales
            ->with('sousTaches')
            ->orderBy('echeance')
            ->get();

        // Répartition Kanban
        $aFaire = $tasks->where('statut', 'a_faire');
        $enCours = $tasks->where('statut', 'en_cours');
        $terminees = $tasks->where('statut', 'terminee');

        return view('gel-secretary.tasks.index', compact('aFaire', 'enCours', 'terminees'));
    }

    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);

        $request->validate([
            'titre' => 'required|string|max:255',
            'priorite' => 'required|in:basse,normale,haute,urgente',
            'echeance' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        DaeTache::create([
            'client_id' => $clientId,
            'titre' => $request->titre,
            'description' => $request->description,
            'priorite' => $request->priorite,
            'statut' => 'a_faire',
            'echeance' => $request->echeance,
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Tâche créée.');
    }

    public function updateStatut(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $task = DaeTache::where('client_id', $clientId)->findOrFail($id);

        $request->validate([
            'statut' => 'required|in:a_faire,en_cours,terminee,annulee',
        ]);

        $task->statut = $request->statut;
        
        if ($request->statut === 'terminee') {
            $task->completed_at = now();
        }

        $task->save();

        return back()->with('success', 'Statut de la tâche mis à jour.');
    }

    public function destroy(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $task = DaeTache::where('client_id', $clientId)->findOrFail($id);
        
        $task->delete();

        return back()->with('success', 'Tâche supprimée.');
    }
}
