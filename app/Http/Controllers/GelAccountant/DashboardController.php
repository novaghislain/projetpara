<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\Cabinet;
use App\Models\Gel\Client;
use App\Models\Gel\EcritureComptable;
use App\Models\Gel\LigneEcriture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        // Si pas de cabinet, on affiche un dashboard basique
        if (!$cabinetId) {
            $stats = [
                'clients_actifs' => 0,
                'ecritures_mois' => 0,
                'en_attente' => 0,
                'balance_equilibree' => false,
                'derniere_ecriture' => null,
                'derniere_ecriture_date' => null,
            ];
            $recentClients = collect([]);
            $recentEcritures = collect([]);
            $clients = collect([]);
            $cabinet = null;

            return view('gel-accountant.dashboard', compact(
                'stats', 'recentClients', 'recentEcritures', 'clients', 'cabinet'
            ));
        }

        $cabinet = Cabinet::find($cabinetId);
        $clientId = $request->get('client_id');

        // Stats
        $clientsQuery = Client::where('cabinet_id', $cabinetId);
        if ($clientId) {
            $clientsQuery->where('id', $clientId);
        }
        $clientsActifs = (clone $clientsQuery)->where('statut', 'actif')->count();

        $ecrituresQuery = EcritureComptable::where('cabinet_id', $cabinetId);
        if ($clientId) {
            $ecrituresQuery->where('client_id', $clientId);
        }

        $ecrituresMois = (clone $ecrituresQuery)
            ->whereMonth('date_ecriture', now()->month)
            ->whereYear('date_ecriture', now()->year)
            ->count();

        $enAttente = (clone $ecrituresQuery)
            ->where('valide', false)
            ->count();

        $lastEcriture = (clone $ecrituresQuery)
            ->latest('date_ecriture')
            ->first();

        // Vérifier équilibre balance
        $totalDebit = (clone $ecrituresQuery)->sum('total_debit');
        $totalCredit = (clone $ecrituresQuery)->sum('total_credit');
        $balanceEquilibree = ($totalDebit === $totalCredit) && $totalDebit > 0;

        $recentClients = (clone $clientsQuery)
            ->latest()
            ->take(5)
            ->get();

        $recentEcritures = (clone $ecrituresQuery)
            ->with(['journal:id,code', 'client:id,nom_entreprise'])
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'clients_actifs' => $clientsActifs,
            'ecritures_mois' => $ecrituresMois,
            'en_attente' => $enAttente,
            'balance_equilibree' => $balanceEquilibree,
            'derniere_ecriture' => $lastEcriture?->libelle,
            'derniere_ecriture_date' => $lastEcriture?->date_ecriture?->format('Y-m-d'),
        ];

        $clients = (clone $clientsQuery)->get(['id', 'nom_entreprise']);

        return view('gel-accountant.dashboard', compact(
            'stats', 'recentClients', 'recentEcritures', 'clients', 'cabinet'
        ));
    }
}
