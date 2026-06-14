<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyProject;
use App\Models\CompanyProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Recupere le client_id de l'utilisateur authentifie.
     */
    private function getClientId()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            abort(403, 'Aucune entreprise associee.');
        }
        return $user->client_id;
    }

    /**
     * Affiche la page Projets.
     */
    public function index()
    {
        $this->getClientId();
        return view('company', ['page' => 'company-projects']);
    }

    // ─── PROJETS ─────────────────────────────────────────────────────────────

    /**
     * API: Liste tous les projets de l'entreprise.
     */
    public function projects()
    {
        $clientId = $this->getClientId();

        $projects = CompanyProject::byClient($clientId)
            ->withCount('tasks')
            ->latest()
            ->get()
            ->map(function ($project) {
                $completedTasks = $project->tasks()->where('status', 'done')->count();
                $totalTasks = $project->tasks_count;

                return [
                    'id'              => $project->id,
                    'name'            => $project->name,
                    'description'     => $project->description,
                    'status'          => $project->status,
                    'priority'        => $project->priority,
                    'start_date'      => $project->start_date?->format('Y-m-d'),
                    'end_date'        => $project->end_date?->format('Y-m-d'),
                    'budget'          => $project->budget ? (float) $project->budget : null,
                    'progress'        => (int) $project->progress,
                    'tasks_count'     => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'created_by'      => $project->created_by,
                    'created_at'      => $project->created_at?->format('d/m/Y'),
                ];
            });

        return response()->json($projects);
    }

    /**
     * API: Cree un projet.
     */
    public function storeProject(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:planning,in_progress,completed,on_hold,cancelled',
            'priority'    => 'required|in:low,medium,high,critical',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'budget'      => 'nullable|numeric|min:0',
            'progress'    => 'nullable|integer|min:0|max:100',
        ]);

        $project = CompanyProject::create(array_merge(
            $validated,
            [
                'client_id'  => $clientId,
                'created_by' => Auth::id(),
            ]
        ));

        return response()->json([
            'message' => 'Projet cree avec succes.',
            'project' => $project->fresh(),
        ], 201);
    }

    /**
     * API: Modifie un projet.
     */
    public function updateProject(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $project = CompanyProject::byClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status'      => 'sometimes|in:planning,in_progress,completed,on_hold,cancelled',
            'priority'    => 'sometimes|in:low,medium,high,critical',
            'start_date'  => 'sometimes|nullable|date',
            'end_date'    => 'sometimes|nullable|date|after_or_equal:start_date',
            'budget'      => 'sometimes|nullable|numeric|min:0',
            'progress'    => 'sometimes|integer|min:0|max:100',
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'Projet mis a jour.',
            'project' => $project->fresh()->loadCount('tasks'),
        ]);
    }

    /**
     * API: Supprime un projet.
     */
    public function destroyProject($id)
    {
        $clientId = $this->getClientId();

        $project = CompanyProject::byClient($clientId)->findOrFail($id);
        $project->delete();

        return response()->json(['message' => 'Projet supprime.']);
    }

    // ─── TACHES ───────────────────────────────────────────────────────────────

    /**
     * API: Liste les taches d'un projet ou de tous les projets.
     */
    public function tasks(Request $request)
    {
        $clientId = $this->getClientId();

        $query = CompanyProjectTask::whereHas('project', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })->with('project:id,name');

        // Filtrer par projet si fourni
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->latest()->get()->map(function ($task) {
            return [
                'id'           => $task->id,
                'project_id'   => $task->project_id,
                'project_name' => $task->project?->name,
                'name'         => $task->name,
                'description'  => $task->description,
                'assigned_to'  => $task->assigned_to,
                'status'       => $task->status,
                'priority'     => $task->priority,
                'due_date'     => $task->due_date?->format('Y-m-d'),
                'completed_at' => $task->completed_at?->format('Y-m-d H:i'),
                'created_by'   => $task->created_by,
                'created_at'   => $task->created_at?->format('d/m/Y'),
            ];
        });

        return response()->json($tasks);
    }

    /**
     * API: Cree une tache.
     */
    public function storeTask(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'project_id'  => 'required|exists:company_projects,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|string|max:255',
            'status'      => 'required|in:todo,in_progress,done',
            'priority'    => 'required|in:low,medium,high',
            'due_date'    => 'nullable|date',
        ]);

        // Verifier que le projet appartient bien au client
        CompanyProject::byClient($clientId)->findOrFail($validated['project_id']);

        $taskData = $validated;
        if ($taskData['status'] === 'done') {
            $taskData['completed_at'] = now();
        }
        $taskData['created_by'] = Auth::id();

        $task = CompanyProjectTask::create($taskData);

        return response()->json([
            'message' => 'Tache creee avec succes.',
            'task'    => $task->fresh(['project:id,name']),
        ], 201);
    }

    /**
     * API: Modifie une tache.
     */
    public function updateTask(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $task = CompanyProjectTask::whereHas('project', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })->findOrFail($id);

        $validated = $request->validate([
            'project_id'  => 'sometimes|exists:company_projects,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'assigned_to' => 'sometimes|nullable|string|max:255',
            'status'      => 'sometimes|in:todo,in_progress,done',
            'priority'    => 'sometimes|in:low,medium,high',
            'due_date'    => 'sometimes|nullable|date',
        ]);

        // Si le statut passe a "done" et que completed_at est null, le definir
        if (isset($validated['status']) && $validated['status'] === 'done' && !$task->completed_at) {
            $validated['completed_at'] = now();
        }

        // Si le statut n'est plus "done", reinitialiser completed_at
        if (isset($validated['status']) && $validated['status'] !== 'done') {
            $validated['completed_at'] = null;
        }

        // Verifier le projet si modifie
        if (isset($validated['project_id'])) {
            CompanyProject::byClient($clientId)->findOrFail($validated['project_id']);
        }

        $task->update($validated);

        return response()->json([
            'message' => 'Tache mise a jour.',
            'task'    => $task->fresh(['project:id,name']),
        ]);
    }

    /**
     * API: Supprime une tache.
     */
    public function deleteTask($id)
    {
        $clientId = $this->getClientId();

        $task = CompanyProjectTask::whereHas('project', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })->findOrFail($id);

        $task->delete();

        return response()->json(['message' => 'Tache supprimee.']);
    }

    // ─── STATISTIQUES ─────────────────────────────────────────────────────────

    /**
     * API: Statistiques des projets.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $projects = CompanyProject::byClient($clientId)->get();
        $totalProjects = $projects->count();
        $activeProjects = $projects->whereIn('status', ['planning', 'in_progress'])->count();

        // Repartition par statut
        $byStatus = $projects->groupBy('status')->map(function ($group) {
            return $group->count();
        });

        // Repartition par priorite
        $byPriority = $projects->groupBy('priority')->map(function ($group) {
            return $group->count();
        });

        // Budget total
        $totalBudget = $projects->sum('budget');

        // Progression moyenne
        $avgProgress = $totalProjects > 0 ? round($projects->avg('progress'), 1) : 0;

        // Taches globales
        $totalTasks = CompanyProjectTask::whereHas('project', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })->count();

        $completedTasks = CompanyProjectTask::whereHas('project', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })->where('status', 'done')->count();

        $pendingTasks = CompanyProjectTask::whereHas('project', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })->where('status', 'todo')->count();

        // Taches en retard
        $overdueTasks = CompanyProjectTask::whereHas('project', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })
        ->whereIn('status', ['todo', 'in_progress'])
        ->where('due_date', '<', now())
        ->count();

        $stats = [
            'total_projects'  => $totalProjects,
            'active_projects' => $activeProjects,
            'by_status'       => $byStatus,
            'by_priority'     => $byPriority,
            'total_budget'    => (float) $totalBudget,
            'avg_progress'    => $avgProgress,
            'total_tasks'     => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks'   => $pendingTasks,
            'overdue_tasks'   => $overdueTasks,
        ];

        return response()->json($stats);
    }
}
