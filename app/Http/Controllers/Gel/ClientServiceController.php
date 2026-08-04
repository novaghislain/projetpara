<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Service;
use App\Models\ClientService;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des services attachés aux clients.
 * Permet d'attacher, détacher et mettre à jour le statut des services
 * pour un client donné via l'API.
 */
class ClientServiceController extends Controller
{
    /**
     * API : Liste tous les services d'un client avec les données pivot.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAll($clientId)
    {
        $client = Client::findOrFail($clientId);

        $services = $client->services()
            ->withPivot(['status', 'start_date', 'end_date', 'settings'])
            ->get();

        return response()->json($services);
    }

    /**
     * API : Attache un service à un client.
     * Vérifie que le service n'est pas déjà attaché avant de créer la liaison.
     *
     * @param Request $request La requête HTTP avec service_id, status, dates et settings
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse
     */
    public function attach(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'status' => 'nullable|string|in:actif,inactif,suspendu',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'settings' => 'nullable|json',
        ]);

        // Vérifier si le service est déjà attaché pour éviter les doublons
        if ($client->services()->where('service_id', $validated['service_id'])->exists()) {
            return response()->json(['message' => 'Ce service est déjà attaché à ce client'], 409);
        }

        $client->services()->attach($validated['service_id'], [
            'status' => $validated['status'] ?? 'actif',
            'start_date' => $validated['start_date'] ?? now(),
            'end_date' => $validated['end_date'] ?? null,
            'settings' => $validated['settings'] ?? null,
        ]);

        return response()->json(['message' => 'Service attaché avec succès'], 201);
    }

    /**
     * API : Détache un service d'un client.
     *
     * @param int $clientId L'identifiant du client
     * @param int $serviceId L'identifiant du service à détacher
     * @return \Illuminate\Http\JsonResponse
     */
    public function detach($clientId, $serviceId)
    {
        $client = Client::findOrFail($clientId);

        $client->services()->detach($serviceId);

        return response()->json(['message' => 'Service détaché avec succès']);
    }

    /**
     * API : Met à jour le statut et les paramètres d'un service client.
     *
     * @param Request $request La requête HTTP avec status, dates et settings
     * @param int $clientId L'identifiant du client
     * @param int $serviceId L'identifiant du service
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $clientId, $serviceId)
    {
        $client = Client::findOrFail($clientId);

        $validated = $request->validate([
            'status' => 'required|string|in:actif,inactif,suspendu',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'settings' => 'nullable|json',
        ]);

        $updateData = ['status' => $validated['status']];
        if (isset($validated['start_date'])) $updateData['start_date'] = $validated['start_date'];
        if (isset($validated['end_date'])) $updateData['end_date'] = $validated['end_date'];
        if (isset($validated['settings'])) $updateData['settings'] = $validated['settings'];

        $client->services()->updateExistingPivot($serviceId, $updateData);

        return response()->json(['message' => 'Statut mis à jour avec succès']);
    }
}
