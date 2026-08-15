<?php

namespace App\Http\Controllers\GelAccountant\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de sélection d'un client par un comptable.
 *
 * Permet au comptable de « basculer » sur l'espace d'un client
 * en stockant son identifiant en session. Le middleware
 * CheckClientAccess exploite ensuite cette session pour filtrer
 * toutes les requêtes comptables.
 */
class ClientSelectionController extends Controller
{
    /**
     * Sélectionne un client et redirige vers son tableau de bord.
     *
     * Vérifie que le client appartient au cabinet du comptable
     * et qu'une invitation acceptée existe avant de stocker
     * le client_id en session.
     *
     * @param int $clientId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function select($clientId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $entrepriseId = $user->entreprises->first()->id ?? null;

        // Le client doit appartenir au cabinet du comptable
        $client = \App\Models\Client::where('id', $clientId)
            ->where('entreprise_id', $entrepriseId)
            ->firstOrFail();

        // Mettre à jour active_client_id sur l'utilisateur
        // $user->active_client_id n'existe plus dans la nouvelle architecture, 
        // on se fie uniquement à la session
        
        session([
            'current_client_id'   => $client->id,
            'current_client_name' => $client->nom_entreprise,
            'active_client_id'    => $client->id,
        ]);

        return redirect()->route('gel-accountant.dashboard')
            ->with('success', 'Vous travaillez maintenant sur : ' . $client->nom_entreprise);
    }

    /**
     * Quitte le contexte client en cours (retour au dashboard cabinet).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deselect()
    {
        session()->forget(['current_client_id', 'current_client_name', 'active_client_id']);

        return redirect()->route('gel-accountant.dashboard')
            ->with('info', 'Contexte client désactivé.');
    }
}
