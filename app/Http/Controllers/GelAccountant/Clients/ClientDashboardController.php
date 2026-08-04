<?php

namespace App\Http\Controllers\GelAccountant\Clients;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Gel\EcritureComptable;
use App\Models\Gel\ExerciceComptable;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur du tableau de bord d'un client sélectionné.
 *
 * Affiche les indicateurs clés (KPI) comptables propres au client
 * actuellement sélectionné par le comptable : exercices, écritures,
 * balance, etc.
 */
class ClientDashboardController extends Controller
{
    /**
     * Affiche le tableau de bord du client sélectionné.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;
        $clientId = session('current_client_id');

        if (!$clientId) {
            return redirect()->route('gel-accountant.clients')
                ->with('error', 'Veuillez sélectionner un client.');
        }

        $client = Client::where('id', $clientId)
            ->where('cabinet_id', $cabinetId)
            ->firstOrFail();

        // ─── KPIs ───────────────────────────────────────────────
        // Exercice en cours
        $exerciceActif = ExerciceComptable::withoutGlobalScopes()
            ->where('cabinet_id', $cabinetId)
            ->where('client_id', $clientId)
            ->where('cloture', false)
            ->orderByDesc('date_fin')
            ->first();

        // Nombre d'écritures (total + ce mois)
        $ecrituresTotal = EcritureComptable::withoutGlobalScopes()
            ->where('cabinet_id', $cabinetId)
            ->where('client_id', $clientId)
            ->count();

        $ecrituresMois = EcritureComptable::withoutGlobalScopes()
            ->where('cabinet_id', $cabinetId)
            ->where('client_id', $clientId)
            ->whereMonth('date_ecriture', now()->month)
            ->whereYear('date_ecriture', now()->year)
            ->count();

        // Écritures en attente de validation
        $enAttente = EcritureComptable::withoutGlobalScopes()
            ->where('cabinet_id', $cabinetId)
            ->where('client_id', $clientId)
            ->where('valide', false)
            ->count();

        // Équilibre de la balance (somme débit == somme crédit sur écritures validées)
        $totaux = EcritureComptable::withoutGlobalScopes()
            ->where('cabinet_id', $cabinetId)
            ->where('client_id', $clientId)
            ->where('valide', true)
            ->selectRaw('COALESCE(SUM(total_debit), 0) as total_debit, COALESCE(SUM(total_credit), 0) as total_credit')
            ->first();

        $balanceEquilibree = ($totaux->total_debit ?? 0) == ($totaux->total_credit ?? 0);

        // Nombre de comptes actifs
        $comptesActifs = CompteComptable::withoutGlobalScopes()
            ->where('cabinet_id', $cabinetId)
            ->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhereNull('client_id'); // comptes partagés (SYSCOHADA)
            })
            ->where('actif', true)
            ->count();

        // Dernières écritures
        $dernieresEcritures = EcritureComptable::withoutGlobalScopes()
            ->where('cabinet_id', $cabinetId)
            ->where('client_id', $clientId)
            ->with(['journal:id,code', 'createur:id,name'])
            ->orderByDesc('date_ecriture')
            ->limit(10)
            ->get();

        // Nombre de journaux
        $journauxCount = Journal::withoutGlobalScopes()
            ->where('cabinet_id', $cabinetId)
            ->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhereNull('client_id');
            })
            ->count();

        $stats = [
            'ecritures_total'      => $ecrituresTotal,
            'ecritures_mois'       => $ecrituresMois,
            'en_attente'           => $enAttente,
            'balance_equilibree'   => $balanceEquilibree,
            'total_debit'          => $totaux->total_debit ?? 0,
            'total_credit'         => $totaux->total_credit ?? 0,
            'comptes_actifs'       => $comptesActifs,
            'journaux_count'       => $journauxCount,
        ];

        return view('gel-accountant.clients.dashboard', compact(
            'client',
            'exerciceActif',
            'stats',
            'dernieresEcritures'
        ) + [
            'currentSection' => 'clients',
            'currentPage'    => 'client-dashboard',
        ]);
    }
}
