<?php

namespace App\Http\Controllers\GelSecretary\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Gel\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogService;

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

        if ($user->isAutonomousSecretary()) {
            $query = Task::whereNull('cabinet_id')
                ->where('created_by', $user->id)
                ->orderBy('date_echeance', 'asc');
        } else {
            $cabinetId = $user->cabinet_id;
            $query = Task::where('cabinet_id', $cabinetId)
                ->with(['assigne:id,name', 'client:id,company_name,nom_entreprise'])
                ->orderBy('date_echeance', 'asc');
        }

        $allTasks = $query->get();

        $tasksTodo = $allTasks->where('statut', 'a_faire');
        $tasksInProgress = $allTasks->where('statut', 'en_cours');
        $tasksToValidate = $allTasks->where('statut', 'a_valider');
        $tasksDone = $allTasks->where('statut', 'terminee');

        // Statistiques globales pour les indicateurs du tableau de bord
        $stats = [
            'a_faire' => $tasksTodo->count(),
            'en_cours' => $tasksInProgress->count(),
            'a_valider' => $tasksToValidate->count(),
            'terminees' => $tasksDone->count(),
            // Tâches dont l'échéance est dépassée et qui ne sont pas terminées
            'echues' => $allTasks->whereIn('statut', ['a_faire', 'en_cours', 'a_valider'])
                ->filter(function($t) { return $t->date_echeance && \Carbon\Carbon::parse($t->date_echeance)->isPast(); })
                ->count(),
        ];

        return view('gel-secretary.tasks.index', compact('tasksTodo', 'tasksInProgress', 'tasksToValidate', 'tasksDone', 'stats') + ['currentSection' => 'tasks', 'currentPage' => 'taches']);
    }

    public function changeStatus(Request $request, $id, $status)
    {
        $task = Task::findOrFail($id);
        if (in_array($status, ['a_faire', 'en_cours', 'a_valider', 'terminee'])) {
            $task->statut = $status;
            if ($status === 'terminee') {
                $task->termine_at = now();
            } else {
                $task->termine_at = null;
            }
            $task->save();
        // Log status change
        \App\Services\AuditLogService::log('task.status_changed', $task, null, ['new_status' => $status]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task]);
        }

        return redirect()->route('gel-secretary.tasks.index')
            ->with('success', 'Statut de la tâche mis à jour.');
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
            'client_id' => 'nullable|exists:clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priorite' => 'required|in:basse,moyenne,haute,critique',
            'date_echeance' => 'nullable|date',
        ]);

        if ($user->isAutonomousSecretary()) {
            $validated['cabinet_id'] = null;
        } else {
            $validated['cabinet_id'] = $user->cabinet_id;
        }

        $validated['created_by'] = $user->id;
        $validated['statut'] = 'a_faire';

        $task = Task::create($validated);
        // Log task creation
        \App\Services\AuditLogService::log('task.created', $task, null, $validated);

        return redirect()->route('gel-secretary.tasks.index')
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
        // Log task update
        \App\Services\AuditLogService::log('task.updated', $task, null, $validated);

        return redirect()->route('gel-secretary.tasks.index')
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
        // Log task deletion
        \App\Services\AuditLogService::log('task.deleted', $task);

        return redirect()->route('gel-secretary.tasks.index')
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

        return redirect()->route('gel-secretary.tasks.index')
            ->with('success', 'Statut de la tâche mis à jour.');
    }
}
