<?php

namespace App\Http\Controllers\GelBusiness\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\LigneEcriture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de la balance comptable pour l'espace Gel Business.
 *
 * La balance est un état récapitulatif qui présente, pour chaque compte,
 * le total des mouvements au débit et au crédit ainsi que le solde débiteur
 * ou créditeur sur une période donnée.
 */
class BalanceController extends Controller
{
    /**
     * Affiche la balance comptable pour le client connecté.
     *
     * Récupère tous les comptes actifs du cabinet et calcule pour chacun
     * les totaux débit/crédit ainsi que les soldes à une date donnée.
     * Les comptes sans mouvement sont exclus du résultat.
     *
     * @param  \Illuminate\Http\Request  $request  Peut contenir 'date_fin' pour filtrer la période
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Déterminer l'ID client à partir de l'utilisateur connecté
        $clientId = $user->client_id ?? $user->active_client_id;

        // Aucun client associé -> retourner une balance vide
        if (!$clientId) {
            return view('gel-business.comptabilite.balance.index', ['currentSection' => 'comptabilite'] + [
                'balanceData' => [],
                'totalDebit' => 0,
                'totalCredit' => 0,
            ]);
        }

        // Récupérer le cabinet associé au client
        $client = \App\Models\Gel\Client::find($clientId);
        $cabinetId = $client->cabinet_id;

        // Date de fin par défaut : aujourd'hui
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));

        // Charger tous les comptes actifs du cabinet (hors comptes de niveau 0)
        $comptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get();

        $balanceData = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // Pour chaque compte, calculer les totaux débit/crédit sur la période
        foreach ($comptes as $compte) {
            // Requête de base : lignes d'écritures validées du client dans la période
            $lignesQuery = LigneEcriture::where('compte_id', $compte->id)
                ->whereHas('ecriture', function ($q) use ($cabinetId, $clientId, $dateFin) {
                    $q->where('cabinet_id', $cabinetId)
                      ->where('client_id', $clientId)
                      ->where('valide', true)
                      ->where('date_ecriture', '<=', $dateFin);
                });

            // Somme des montants au débit et au crédit
            $td = (clone $lignesQuery)->where('sens', 'debit')->sum('montant');
            $tc = (clone $lignesQuery)->where('sens', 'credit')->sum('montant');

            // Ignorer les comptes sans mouvement
            if ($td === 0 && $tc === 0) continue;

            // Calcul du solde : différence entre débit et crédit
            $sd = $td >= $tc ? $td - $tc : 0;  // Solde débiteur
            $sc = $tc > $td ? $tc - $td : 0;   // Solde créditeur

            // Cumul des totaux généraux
            $totalDebit += $td;
            $totalCredit += $tc;

            $balanceData[] = [
                'code' => $compte->code,
                'intitule' => $compte->intitule,
                'total_debit' => $td,
                'total_credit' => $tc,
                'solde_debit' => $sd,
                'solde_credit' => $sc,
            ];
        }

        return view('gel-business.comptabilite.balance.index', compact('balanceData', 'totalDebit', 'totalCredit') + ['currentSection' => 'comptabilite']);
    }
}
