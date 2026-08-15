<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Client;
use App\Models\Facture;
use App\Models\Entreprise;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Get the active enterprise ID from session
        $entrepriseId = session('active_entreprise_id');
        
        if (!$entrepriseId) {
            return redirect()->route('dashboard')->with('error', 'Aucune entreprise sélectionnée.');
        }

        $entreprise = Entreprise::find($entrepriseId);

        // KPIs
        $totalClients = Client::where('entreprise_id', $entrepriseId)->count();
        $chiffreAffaires = Facture::where('entreprise_id', $entrepriseId)
                                  ->where('statut', 'payee')
                                  ->sum('montant_ht');
                                  
        $facturesEnAttente = Facture::where('entreprise_id', $entrepriseId)
                                    ->whereIn('statut', ['envoyee', 'en_retard'])
                                    ->sum('montant_ttc');

        $recentClients = Client::where('entreprise_id', $entrepriseId)
                               ->orderBy('created_at', 'desc')
                               ->take(5)
                               ->get();

        $recentFactures = Facture::where('entreprise_id', $entrepriseId)
                                 ->with('client')
                                 ->orderBy('created_at', 'desc')
                                 ->take(5)
                                 ->get();

        // Extra metrics for Direction Dashboard
        $totalTeam = \App\Models\Affectation::where('entreprise_id', $entrepriseId)->whereIn('statut', ['actif', 'active'])->count();
        $masseSalariale = 0; // À lier avec gel_salaries
        
        $congesEnAttente = \DB::table('rh_leave_requests')->where('statut', 'pending')->count();
        $tachesEnAttente = \DB::table('gel_tasks')->where('statut', 'en_attente_validation')->count();
        $totalValidations = $congesEnAttente + $tachesEnAttente;

        return view('gel-direction.dashboard', compact(
            'entreprise',
            'totalClients',
            'chiffreAffaires',
            'facturesEnAttente',
            'recentClients',
            'recentFactures',
            'totalTeam',
            'masseSalariale',
            'totalValidations'
        ));
    }
}
