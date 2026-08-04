<?php

namespace App\Http\Controllers\GelAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cabinet = $user->cabinet;
        $entityType = 'cabinet';
        
        if (!$cabinet && $user->entreprise) {
            $cabinet = $user->entreprise;
            $entityType = 'entreprise';
        }
        
        if (!$cabinet && $user->client_id) {
            $cabinet = $user->client;
            $entityType = 'client';
        }

        $entityId = $cabinet->id ?? 0;

        // Stats globales (Team, Clients)
        if ($entityType === 'client') {
            $teamCount = User::where('client_id', $entityId)->count();
            $clientsCount = 0; // Un client final n'a pas de sous-clients
        } elseif ($entityType === 'entreprise') {
            $teamCount = User::where('entreprise_id', $entityId)->count();
            $clientsCount = 0; 
        } else {
            $teamCount = User::where('cabinet_id', $entityId)->count();
            $clientsCount = Client::where('created_by', $user->id)->count(); 
        }

        return view('gel-admin.dashboard.index', compact('cabinet', 'teamCount', 'clientsCount'));
    }
}
