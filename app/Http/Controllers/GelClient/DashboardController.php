<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord du client.
     */
    public function index()
    {
        $clientId = session('active_client_id');

        if (!$clientId) {
            return redirect()->route('dashboard')->with('error', 'Veuillez sélectionner une entreprise.');
        }

        // Statistiques de base pour le client
        $stats = [
            'ca_mensuel' => 0, // À connecter aux factures du client
            'depenses_mensuelles' => 0, // À connecter aux dépenses
            'documents_recents' => DB::table('dae_documents')
                                    ->where('client_id', $clientId)
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get(),
            'messages_non_lus' => DB::table('dae_messages')
                                    ->where('client_id', $clientId)
                                    ->where('destinataire', 'client')
                                    ->whereNull('read_at')
                                    ->count(),
            'taches_en_attente' => DB::table('dae_tasks')
                                    ->where('client_id', $clientId)
                                    ->where('status', '!=', 'completed')
                                    ->count()
        ];

        return view('gel-client.dashboard', compact('stats'));
    }
}
