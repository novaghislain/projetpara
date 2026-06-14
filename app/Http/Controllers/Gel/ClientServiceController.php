<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Service;
use App\Models\ClientService;
use Illuminate\Http\Request;

class ClientServiceController extends Controller
{
    /**
     * API: Liste des services d'un client.
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
     * API: Attacher un service à un client.
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

        // Vérifier si déjà attaché
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
     * API: Détacher un service d'un client.
     */
    public function detach($clientId, $serviceId)
    {
        $client = Client::findOrFail($clientId);

        $client->services()->detach($serviceId);

        return response()->json(['message' => 'Service détaché avec succès']);
    }

    /**
     * API: Mettre à jour le statut d'un service client.
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
