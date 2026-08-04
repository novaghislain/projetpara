<?php

namespace App\Http\Controllers\GelAccountant\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Gel\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des tâches du cabinet.
 *
 * Permet le suivi des tâches (CRUD) avec des filtres par statut
 * et priorité. Chaque tâche peut être affectée à un collaborateur
 * et associée à un client. Le tableau de bord des statistiques
 * (à faire, en cours, terminées, échues) est calculé à chaque
 * affichage de la liste.
 */
class TasksController extends Controller
{
    /**
     * Affiche la liste paginée des tâches du cabinet.
     *
     * Les tâches peuvent être filtrées par statut et par priorité.
     * Les statistiques globales (à faire, en cours, terminées,
     * échues) sont calculées et transmises à la vue.
     *
     * @param  Request $request La requête avec les filtres optionnels
     *                          `statut` et `priorite`.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = Task::where('cabinet_id', $cabinetId);

        // Filtre par statut (a_faire, en_cours, terminee)
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par priorité (basse, moyenne, haute, critique)
        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        $tasks = $query->with(['assigne:id,name', 'client:id,nom_entreprise'])
            ->orderBy('date_echeance')
            ->paginate(20);

        // Statistiques globales pour les indicateurs du tableau de bord
        $stats = [
            'a_faire' => Task::where('cabinet_id', $cabinetId)->where('statut', 'a_faire')->count(),
            'en_cours' => Task::where('cabinet_id', $cabinetId)->where('statut', 'en_cours')->count(),
            'terminees' => Task::where('cabinet_id', $cabinetId)->where('statut', 'terminee')->count(),
            // Tâches dont l'échéance est dépassée et qui ne sont pas terminées
            'echues' => Task::where('cabinet_id', $cabinetId)
                ->whereIn('statut', ['a_faire', 'en_cours'])
                ->where('date_echeance', '<', now())
                ->count(),
        ];

        return view('gel-accountant.tasks.index', compact('tasks', 'stats') + ['currentSection' => 'tasks', 'currentPage' => 'taches']);
    }

    /**
     * Crée une nouvelle tâche dans le cabinet.
     *
     * Rattache la tâche au cabinet de l'utilisateur, définit le
     * créateur et initialise le statut à "a_faire".
     *
     * @param  Request $request La requête contenant les données de la tâche.
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Met à jour une tâche existante.
     *
     * Si le statut passe à "terminee", la date de terminaison est
     * automatiquement enregistrée.
     *
     * @param  Request $request La requête contenant les champs modifiés.
     * @param  int     $id      L'identifiant de la tâche à modifier.
     * @return \Illuminate\Http\RedirectResponse
     */
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

        // Horodatage automatique de la terminaison
        if ($validated['statut'] === 'terminee') {
            $validated['termine_at'] = now();
        }

        $task->update($validated);

        return redirect()->route('gel-accountant.tasks')
            ->with('success', 'Tâche mise à jour.');
    }

    /**
     * Supprime une tâche.
     *
     * @param  int $id L'identifiant de la tâche à supprimer.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()->route('gel-accountant.tasks')
            ->with('success', 'Tâche supprimée.');
    }

    /**
     * Bascule le statut d'une tâche dans le cycle :
     * "a_faire" -> "en_cours" -> "terminee" -> "a_faire".
     *
     * @param  int $id L'identifiant de la tâche.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $task = Task::findOrFail($id);
        $task->statut = match ($task->statut) {
            'a_faire' => 'en_cours',
            'en_cours' => 'terminee',
            default => 'a_faire',
        };
        // Enregistrement de la date de terminaison si le statut devient "terminee"
        if ($task->statut === 'terminee') {
            $task->termine_at = now();
        }
        $task->save();

        return redirect()->route('gel-accountant.tasks')
            ->with('success', 'Statut de la tâche mis à jour.');
    }
}
