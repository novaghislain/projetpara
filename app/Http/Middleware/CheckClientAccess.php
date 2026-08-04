<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Gel\Client;
use App\Models\Gel\ClientInvitation;

class CheckClientAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // This middleware is meant for accountants accessing a client
        if (!$user || (!$user->isAccountant() && !$user->cabinet_id)) {
            return $next($request);
        }

        $clientId = $request->route('client_id') 
                 ?? $request->input('client_id')
                 ?? $user->active_client_id
                 ?? session('current_client_id');

        if (!$clientId) {
            // Règle 2 : Aucun client sélectionné
            abort(403, 'Aucun client sélectionné. Veuillez sélectionner une entreprise cliente.');
        }

        // Vérifier que le client appartient au cabinet du comptable ou que le user a un rôle
        $client = Client::where('id', $clientId)
            ->where(function($q) use ($user) {
                $q->where('cabinet_id', $user->cabinet_id)
                  ->orWhereIn('id', function($sub) use ($user) {
                      $sub->select('client_id')->from('user_clients')->where('user_id', $user->id);
                  });
            })
            ->first();

        if (!$client) {
            abort(403, 'Ce client n\'appartient pas à votre cabinet.');
        }

        // Si le client a été créé par ce cabinet, pas besoin d'invitation.
        $invitation = null;
        if ($client->cabinet_id !== $user->cabinet_id) {
            // Vérifier que le client a accepté l'invitation
            $invitation = ClientInvitation::where('cabinet_id', $user->cabinet_id)
                ->where('client_id', $clientId)
                ->where('statut', 'acceptee')
                ->first();

            if (!$invitation) {
                abort(403, 'Accès refusé : invitation non acceptée ou révoquée par le client.');
            }

            // Vérifier que l'invitation n'est pas expirée
            if ($invitation->expire_at && $invitation->expire_at < now()) {
                abort(403, 'Accès refusé : invitation expirée.');
            }
        }

        // Injecter le client dans la requête
        $request->merge(['client' => $client, 'invitation' => $invitation]);
        $request->attributes->set('client_id', $clientId);
        $request->attributes->set('client', $client);
        
        // Session stricte (Règle Supplémentaire)
        session(['current_client_id' => $clientId]);

        return $next($request);
    }
}
