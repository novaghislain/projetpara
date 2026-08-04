<?php

namespace App\Http\Controllers\GelBusiness\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\CompteComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du grand livre comptable pour l'espace Gel Business.
 *
 * Le grand livre présente l'ensemble des mouvements (lignes d'écritures)
 * classés par compte, avec possibilité de filtrage par compte et par période.
 * Il permet de visualiser le détail chronologique des opérations validées.
 */
class GrandLivreController extends Controller
{
    /**
     * Affiche le grand livre comptable.
     *
     * Récupère toutes les lignes d'écritures validées du client,
     * avec possibilité de filtrer par compte et par intervalle de dates.
     * Fournit également la liste des comptes disponibles pour le filtre.
     *
     * @param  \Illuminate\Http\Request  $request  Peut contenir 'compte_id', 'date_from', 'date_to'
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Déterminer l'ID client à partir de l'utilisateur connecté
        $clientId = $user->client_id ?? $user->active_client_id;

        // Aucun client associé -> retourner une vue vide
        if (!$clientId) {
            return view('gel-business.comptabilite.grand-livre.index', ['currentSection' => 'comptabilite'] + [
                'lignes' => collect([]),
                'comptes' => collect([]),
            ]);
        }

        // Récupérer le cabinet associé au client
        $client = \App\Models\Gel\Client::find($clientId);
        $cabinetId = $client->cabinet_id;

        // Requête de base : toutes les lignes d'écritures validées du client
        $query = LigneEcriture::whereHas('ecriture', function ($q) use ($cabinetId, $clientId) {
            $q->where('cabinet_id', $cabinetId)
              ->where('client_id', $clientId)
              ->where('valide', true);
        })->with(['ecriture.journal', 'compte']);

        // Filtre optionnel par compte comptable
        if ($compteId = $request->input('compte_id')) {
            $query->where('compte_id', $compteId);
        }

        // Filtre optionnel par date de début
        if ($dateFrom = $request->input('date_from')) {
            $query->whereHas('ecriture', function ($q) use ($dateFrom) {
                $q->where('date_ecriture', '>=', $dateFrom);
            });
        }

        // Filtre optionnel par date de fin
        if ($dateTo = $request->input('date_to')) {
            $query->whereHas('ecriture', function ($q) use ($dateTo) {
                $q->where('date_ecriture', '<=', $dateTo);
            });
        }

        // Récupération des lignes triées par écriture
        $lignes = $query->orderBy('ecriture_id')->get();

        // Liste des comptes actifs pour le filtre déroulant
        $comptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'intitule']);

        return view('gel-business.comptabilite.grand-livre.index', compact('lignes', 'comptes') + ['currentSection' => 'comptabilite']);
    }
}
