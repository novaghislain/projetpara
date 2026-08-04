<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Gel\Task;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Statistiques globales "Direction"
        $totalClients = Client::where('cabinet_id', $user->cabinet_id)->count();
        
        // Tâches assignées au dirigeant (ex: validations)
        $validationsCount = Task::where('assigned_to', $user->id)
                                ->where('statut', 'a_faire')
                                ->count();
                                
        // Productivité globale de l'équipe
        $completedTasksCount = Task::where('cabinet_id', $user->cabinet_id)
                                   ->where('statut', 'termine')
                                   ->count();

        return view('gel-direction.dashboard', compact('totalClients', 'validationsCount', 'completedTasksCount'));
    }
}
