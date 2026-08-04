<?php

namespace App\Http\Controllers\Traits;

use App\Models\Gel\Client;

trait ClientScopedController
{
    /**
     * Get the active client from route, request, or session, and ensure access.
     */
    protected function getClientOrFail(): Client
    {
        $clientId = request()->route('client_id')
                  ?? request()->input('client_id')
                  ?? session('current_client_id');

        if (!$clientId) {
            // Rediriger vers la page de sélection de client
            abort(redirect()->route('gel-accountant.clients.index')
                ->with('error', 'Veuillez sélectionner une entreprise cliente'));
        }

        $client = Client::where('id', $clientId)
            ->where('cabinet_id', auth()->user()->cabinet_id)
            ->whereHas('invitations', function ($q) {
                $q->where('statut', 'acceptee');
            })
            ->firstOrFail();

        session(['current_client_id' => $clientId]);
        request()->merge(['client' => $client]);
        request()->attributes->set('client_id', $clientId);

        return $client;
    }

    /**
     * Get the active client ID.
     */
    protected function getClientId(): int
    {
        return $this->getClientOrFail()->id;
    }
}
