<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class EmecefController extends Controller
{
    /**
     * Récupère le client_id de l'utilisateur authentifié.
     */
    private function getClient()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            abort(403, 'Aucune entreprise associée.');
        }
        return $user->client;
    }

    /**
     * Affiche la page de configuration e-MECeF.
     */
    public function index()
    {
        $client = $this->getClient();
        return view('company', [
            'page' => 'company-emecef',
            'clientId' => $client->id,
        ]);
    }

    /**
     * API: Retourne la configuration actuelle e-MECeF.
     */
    public function status()
    {
        $client = $this->getClient();

        return response()->json([
            'configured' => $client->emecef_is_active && !empty($client->emecef_nim) && !empty($client->emecef_password),
            'emecef_nim' => $client->emecef_nim,
            'emecef_is_active' => $client->emecef_is_active,
            'has_password' => !empty($client->emecef_password),
            'updated_at' => $client->updated_at,
        ]);
    }

    /**
     * API: Sauvegarde la configuration e-MECeF (NIM + mot de passe).
     */
    public function configure(Request $request)
    {
        $client = $this->getClient();

        $validated = $request->validate([
            'emecef_nim' => 'required|string|max:50',
            'emecef_password' => 'required|string|min:4|max:255',
            'emecef_is_active' => 'boolean',
        ]);

        $client->update([
            'emecef_nim' => $validated['emecef_nim'],
            'emecef_password' => $validated['emecef_password'],
            'emecef_is_active' => $validated['emecef_is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuration e-MECeF enregistrée avec succès.',
            'client' => $client->fresh()->only([
                'emecef_nim', 'emecef_is_active', 'updated_at',
            ]),
        ]);
    }

    /**
     * API: Teste la connexion e-MECeF (simulation).
     */
    public function test()
    {
        $client = $this->getClient();

        if (!$client->emecef_is_active || empty($client->emecef_nim) || empty($client->emecef_password)) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration e-MECeF incomplète.',
            ], 422);
        }

        // Simulation : le mot de passe est stocké de manière chiffrée
        // En production, on ferait un appel de test à l'API DGI
        return response()->json([
            'success' => true,
            'message' => 'Connexion e-MECeF réussie (simulation).',
            'nim' => $client->emecef_nim,
        ]);
    }

    /**
     * API: Supprime la configuration e-MECeF.
     */
    public function destroy()
    {
        $client = $this->getClient();

        $client->update([
            'emecef_nim' => null,
            'emecef_password' => null,
            'emecef_is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuration e-MECeF supprimée.',
        ]);
    }
}
