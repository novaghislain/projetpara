<?php

namespace App\Http\Controllers\GelAccountant\Clients;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Gel\ClientInvitation;
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

        // Le client doit appartenir au cabinet du comptable ou être assigné
        $client = Client::where('id', $clientId)
            ->where(function($q) use ($user) {
                if ($user->cabinet_id) {
                    $q->where('cabinet_id', $user->cabinet_id);
                }
                $q->orWhereIn('id', function($sub) use ($user) {
                    $sub->select('client_id')->from('user_clients')->where('user_id', $user->id);
                });
            })
            ->firstOrFail();

        // Si le client n'a pas été créé par ce cabinet, vérifier l'invitation
        if ($client->cabinet_id !== $user->cabinet_id) {
            $invitation = ClientInvitation::where('cabinet_id', $user->cabinet_id)
                ->where('client_id', $clientId)
                ->where('statut', 'acceptee')
                ->first();

            if (!$invitation) {
                return redirect()->route('gel-accountant.clients')
                    ->with('error', 'Impossible d\'accéder à ce client : aucune invitation acceptée.');
            }
        }

        // Mettre à jour active_client_id sur l'utilisateur
        $user->active_client_id = $client->id;
        $user->save();

        // Stocker aussi en session pour rétrocompatibilité
        session([
            'current_client_id'   => $client->id,
            'current_client_name' => $client->nom_entreprise,
        ]);

        \App\Services\AuditLogService::log('client.select', $client, null, [
            'client_id'   => $client->id,
            'client_name' => $client->nom_entreprise,
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
        session()->forget(['current_client_id', 'current_client_name']);

        return redirect()->route('gel-accountant.dashboard')
            ->with('info', 'Contexte client désactivé.');
    }
}
