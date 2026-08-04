<?php

namespace App\Http\Controllers\GelBusiness\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\LigneEcriture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur des états financiers pour l'espace Gel Business.
 *
 * Génère le bilan comptable (actif/passif) et le compte de résultat
 * (produits/charges) à partir des écritures comptables validées
 * du client, en s'appuyant sur la classification SYSCOHADA des comptes.
 */
class EtatsFinanciersController extends Controller
{
    /**
     * Affiche les états financiers (bilan ou compte de résultat).
     *
     * Construit le bilan en classant les comptes par nature (actif, passif)
     * ou le compte de résultat en séparant produits (classe 7) et charges (classe 6),
     * selon le paramètre 'type' passé dans la requête.
     *
     * @param  \Illuminate\Http\Request  $request  Peut contenir 'type' (bilan|resultat) et 'date_fin'
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Déterminer l'ID client et le type d'état demandé
        $clientId = $user->client_id ?? $user->active_client_id;

        $type = $request->input('type', 'bilan');
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));

        // Initialisation des tableaux pour chaque section
        $actif = [];
        $passif = [];
        $produits = [];
        $charges = [];
        $totalProduits = 0;
        $totalCharges = 0;

        // Traitement uniquement si un client est associé
        if ($clientId) {
            $client = \App\Models\Gel\Client::find($clientId);
            $cabinetId = $client->cabinet_id;

            // Récupérer tous les comptes actifs du cabinet
            $comptes = CompteComptable::where('cabinet_id', $cabinetId)
                ->where('actif', true)->where('niveau', '>', 0)->get();

            // Calculer les mouvements pour chaque compte
            foreach ($comptes as $compte) {
                $lignesQuery = LigneEcriture::where('compte_id', $compte->id)
                    ->whereHas('ecriture', function ($q) use ($cabinetId, $clientId, $dateFin) {
                        $q->where('cabinet_id', $cabinetId)
                          ->where('client_id', $clientId)
                          ->where('valide', true)
                          ->where('date_ecriture', '<=', $dateFin);
                    });

                // Totaux débit et crédit pour ce compte
                $td = (clone $lignesQuery)->where('sens', 'debit')->sum('montant');
                $tc = (clone $lignesQuery)->where('sens', 'credit')->sum('montant');

                // Ignorer les comptes sans mouvement
                if ($td === 0 && $tc === 0) continue;

                // Classement selon la classe comptable SYSCOHADA
                if (in_array($compte->classe, ['2', '3', '5']) || ($compte->classe === '4' && $compte->type === 'actif')) {
                    // Comptes d'actif : immobilisations (2), stocks (3), trésorerie (5) et comptes d'actif (4)
                    $actif[] = ['code' => $compte->code, 'intitule' => $compte->intitule, 'brut' => $td, 'amortissement' => 0, 'net' => $td];
                } elseif (in_array($compte->classe, ['1']) || ($compte->classe === '4' && $compte->type === 'passif')) {
                    // Comptes de passif : capitaux propres (1) et comptes de passif (4)
                    $passif[] = ['code' => $compte->code, 'intitule' => $compte->intitule, 'montant' => $tc];
                } elseif ($compte->classe === '7') {
                    // Produits (classe 7) : le solde créditeur constitue le montant
                    $m = $tc - $td;
                    if ($m > 0) { $produits[] = ['code' => $compte->code, 'intitule' => $compte->intitule, 'montant' => $m]; $totalProduits += $m; }
                } elseif ($compte->classe === '6') {
                    // Charges (classe 6) : le solde débiteur constitue le montant
                    $m = $td - $tc;
                    if ($m > 0) { $charges[] = ['code' => $compte->code, 'intitule' => $compte->intitule, 'montant' => $m]; $totalCharges += $m; }
                }
            }
        }

        return view('gel-business.comptabilite.etats-financiers.index', ['currentSection' => 'comptabilite'] + compact(
            'type', 'actif', 'passif', 'produits', 'charges', 'totalProduits', 'totalCharges'
        ));
    }
}
