<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project\Project;
use App\Models\Project\ProjectTask;
use App\Models\Project\Timesheet;

class ProjectController extends Controller
{
    public function createProject(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'name' => 'required|string',
            'start_date' => 'nullable|date',
            'budget' => 'nullable|numeric'
        ]);

        $project = Project::create($request->all());

        return response()->json([
            'status' => 'success',
            'project' => $project,
            'message' => 'Projet créé.'
        ]);
    }

    public function createTask(Request $request, $projectId)
    {
        $request->validate([
            'name' => 'required|string',
            'estimated_hours' => 'nullable|numeric'
        ]);

        $task = ProjectTask::create([
            'project_id' => $projectId,
            'name' => $request->name,
            'description' => $request->description,
            'estimated_hours' => $request->estimated_hours
        ]);

        return response()->json([
            'status' => 'success',
            'task' => $task,
            'message' => 'Tâche créée.'
        ]);
    }

    public function logTime(Request $request, $taskId)
    {
        $request->validate([
            'user_id' => 'required|integer', // ou uuid si l'utilisateur est un uuid
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5'
        ]);

        $timesheet = Timesheet::create([
            'task_id' => $taskId,
            'user_id' => $request->user_id,
            'date' => $request->date,
            'hours' => $request->hours,
            'description' => $request->description
        ]);

        return response()->json([
            'status' => 'success',
            'timesheet' => $timesheet,
            'message' => 'Temps loggé.'
        ]);
    }
}
