<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LicenseController extends Controller
{
    /**
     * Affiche la page de gestion des licences.
     */
    public function index()
    {
        return view('app', ['page' => 'gel-licenses']);
    }

    /**
     * API: Retourne toutes les licences avec les relations client et service, paginées.
     */
    public function listAll()
    {
        // Le super admin (client_id=0) a besoin de voir toutes les licences
        DB::statement("SET app.client_id = '0'");

        $licenses = License::with(['client', 'service'])
            ->latest()
            ->get();

        return response()->json($licenses);
    }

    /**
     * API: Crée une nouvelle licence.
     * Contourne RLS en définissant temporairement le contexte client.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'service_id'     => 'required|exists:services,id',
            'duration_months' => 'required|in:12,24,36',
            'start_date'     => 'required|date',
            'price'          => 'nullable|numeric',
        ]);

        // Calculer la date de fin
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addMonths((int) $validated['duration_months']);

        // Générer la clé de licence
        $licenseKey = strtoupper(
            'GEL-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)
        );

        // Définir le contexte RLS sur le client cible avant l'insertion
        DB::statement("SET app.client_id = '{$validated['client_id']}'");

        try {
            $license = License::create([
                'client_id'       => $validated['client_id'],
                'service_id'      => $validated['service_id'],
                'license_key'     => $licenseKey,
                'duration_months' => $validated['duration_months'],
                'start_date'      => $startDate,
                'end_date'        => $endDate,
                'price'           => $validated['price'] ?? null,
                'status'          => 'active',
            ]);
        } finally {
            // Restaurer le contexte RLS pour l'utilisateur connecté
            DB::statement("SET app.client_id = '" . (auth()->user()->client_id ?? '0') . "'");
        }

        return response()->json($license->load(['client', 'service']), 201);
    }

    /**
     * API: Met à jour une licence (statut principalement).
     * Contourne RLS en définissant temporairement le contexte client.
     */
    public function update(Request $request, $id)
    {
        // Définir le contexte RLS sur 0 (super admin) pour voir toutes les licences
        $originalClientId = auth()->user()->client_id ?? '0';
        DB::statement("SET app.client_id = '0'");

        try {
            $license = License::findOrFail($id);
            // Basculer sur le client de la licence pour pouvoir la modifier
            DB::statement("SET app.client_id = '{$license->client_id}'");

            $validated = $request->validate([
                'status' => 'sometimes|in:active,inactive,expired,suspended',
                'price'  => 'sometimes|nullable|numeric',
            ]);

            $license->update($validated);
            $result = $license->fresh()->load(['client', 'service']);
        } finally {
            DB::statement("SET app.client_id = '{$originalClientId}'");
        }

        return response()->json($result);
    }

    /**
     * API: Supprime une licence.
     * Contourne RLS en définissant temporairement le contexte client.
     */
    public function destroy($id)
    {
        $originalClientId = auth()->user()->client_id ?? '0';
        DB::statement("SET app.client_id = '0'");

        try {
            $license = License::findOrFail($id);
            DB::statement("SET app.client_id = '{$license->client_id}'");
            $license->delete();
        } finally {
            DB::statement("SET app.client_id = '{$originalClientId}'");
        }

        return response()->json(['message' => 'Licence supprimée avec succès.'], 200);
    }
}
