<?php

namespace App\Http\Controllers\GelAccountant\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use Illuminate\Support\Str;

class ClientPortalController extends Controller
{
    /**
     * Afficher les paramètres du portail client.
     */
    public function index()
    {
                $clientId = session('active_client_id') ?? session('current_client_id');
        $client = Client::findOrFail($clientId);

        // Si le slug est vide, le générer
        if (empty($client->portal_slug)) {
            $client->portal_slug = $client->generateUniqueSlug($client->company_name);
            $client->save();
        }

        // URL du portail (TODO: utiliser un env var pour le domaine)
        $domain = env('PORTAL_DOMAIN', 'client.gelsabinet.com');
        $portalUrl = "https://{$domain}/{$client->portal_slug}";
        // Si on est en local, utiliser http://localhost:8000
        if (env('APP_ENV') === 'local') {
            $portalUrl = url("/portal/{$client->portal_slug}");
        }

        // Récupérer les contacts du portail
        $portalContacts = $client->portalContacts()->get();

        return view('gel-accountant.settings.client-portal', compact('client', 'portalUrl', 'portalContacts'));
    }

    /**
     * Régénérer le slug du portail.
     */
    public function regenerateSlug(Request $request)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');
        $client = Client::findOrFail($clientId);

        // On regénère en s'assurant qu'on ne retombe pas sur le même
        $client->portal_slug = $client->generateUniqueSlug($client->company_name . '-' . Str::random(4));
        $client->save();

        return redirect()->back()->with('success', 'Le lien de l\'Espace Client a été régénéré avec succès. L\'ancien lien est désormais inactif.');
    }

    /**
     * Activer / Désactiver le portail.
     */
    public function toggleStatus(Request $request)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');
        $client = Client::findOrFail($clientId);

        $client->portal_active = !$client->portal_active;
        $client->save();

        $status = $client->portal_active ? 'activé' : 'désactivé';
        return redirect()->back()->with('success', "L'Espace Client a été {$status}.");
    }
}
