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
        $totalClients = \App\Models\Client::count();
        $totalUsers = \App\Models\User::count();
        
        // Chiffres factices pour la démonstration des KPIs
        $mrr = 45200; // Monthly Recurring Revenue
        $croissance = 12.5; // %
        $nouveauxClientsMois = 8;
        
        // Tâches assignées au dirigeant (ex: validations)
        // On simule 3 validations en attente
        $validationsCount = 3;
                                
        // Productivité globale de l'équipe (tâches terminées ce mois)
        $completedTasksCount = 142;
        
        // Données pour le graphique (6 derniers mois)
        $chartData = [
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            'revenus' => [38000, 39500, 41000, 40500, 43000, $mrr],
            'depenses' => [25000, 26000, 25500, 27000, 28000, 28500]
        ];

        return view('gel-direction.dashboard', compact(
            'totalClients', 
            'totalUsers', 
            'validationsCount', 
            'completedTasksCount',
            'mrr',
            'croissance',
            'nouveauxClientsMois',
            'chartData'
        ));
    }
}
