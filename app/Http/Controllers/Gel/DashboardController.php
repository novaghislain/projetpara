<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mission;
use App\Models\Pole;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Affiche la page d'accueil publique (landing page).
     */
    public function index()
    {
        return view('landing');
    }

    /**
     * Affiche le tableau de bord (authentifié).
     */
    public function dashboard()
    {
        return view('app', ['page' => 'gel-dashboard']);
    }

    /**
     * API: Retourne les statistiques du tableau de bord.
     */
    public function stats()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'super_admin';
        $isDirector = $user->role === 'director';

        $clientsQuery = Client::query();
        $missionsQuery = Mission::query();
        $polesQuery = Pole::query();

        // Filtres selon le rôle
        if (!$isSuperAdmin && !$isDirector) {
            $poleId = $user->pole_id;
            $clientsQuery->whereHas('poles', fn($q) => $q->where('pole_id', $poleId));
            $missionsQuery->where(function ($q) use ($user, $poleId) {
                $q->where('pole_id', $poleId)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        return response()->json([
            'total_clients' => $clientsQuery->count(),
            'active_clients' => (clone $clientsQuery)->where('status', 'actif')->count(),
            'total_missions' => $missionsQuery->count(),
            'pending_missions' => (clone $missionsQuery)->whereIn('status', ['a_faire', 'en_cours'])->count(),
            'completed_missions' => (clone $missionsQuery)->where('status', 'terminee')->count(),
            'total_poles' => $polesQuery->count(),
        ]);
    }
}
