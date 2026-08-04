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
     * Contrôleur de gestion des licences logicielles.
     * Gère le cycle de vie des licences avec gestion spéciale du contexte RLS
     * (Row-Level Security) pour les opérations cross-clients.
     */

    /**
     * Affiche la page de gestion des licences.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'gel-licenses']);
    }

    /**
     * API : Retourne toutes les licences avec les relations client et service.
     * Le super admin (client_id=0) peut voir toutes les licences via RLS.
     *
     * @return \Illuminate\Http\JsonResponse La liste des licences
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
     * API : Crée une nouvelle licence avec génération de clé.
     * Contourne RLS en définissant temporairement le contexte client cible.
     *
     * @param Request $request La requête HTTP avec les données de la licence
     * @return \Illuminate\Http\JsonResponse La licence créée avec ses relations
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

        // Calcul de la date de fin à partir de la durée
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addMonths((int) $validated['duration_months']);

        // Génération de la clé de licence unique
        $licenseKey = strtoupper(
            'GEL-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)
        );

        // Changement du contexte RLS vers le client cible pour l'insertion
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
            // Restauration du contexte RLS pour l'utilisateur connecté
            DB::statement("SET app.client_id = '" . (auth()->user()->client_id ?? '0') . "'");
        }

        return response()->json($license->load(['client', 'service']), 201);
    }

    /**
     * API : Met à jour une licence (statut principalement).
     * Contourne RLS en définissant temporairement le contexte client.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant de la licence
     * @return \Illuminate\Http\JsonResponse La licence mise à jour
     */
    public function update(Request $request, $id)
    {
        // Sauvegarde du contexte RLS d'origine
        $originalClientId = auth()->user()->client_id ?? '0';
        DB::statement("SET app.client_id = '0'");

        try {
            $license = License::findOrFail($id);
            // Bascule sur le client propriétaire de la licence pour modification
            DB::statement("SET app.client_id = '{$license->client_id}'");

            $validated = $request->validate([
                'status' => 'sometimes|in:active,inactive,expired,suspended',
                'price'  => 'sometimes|nullable|numeric',
            ]);

            $license->update($validated);
            $result = $license->fresh()->load(['client', 'service']);
        } finally {
            // Restauration du contexte RLS d'origine
            DB::statement("SET app.client_id = '{$originalClientId}'");
        }

        return response()->json($result);
    }

    /**
     * API : Supprime une licence.
     * Contourne RLS en définissant temporairement le contexte client.
     *
     * @param int $id L'identifiant de la licence
     * @return \Illuminate\Http\JsonResponse Message de confirmation
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
            // Restauration du contexte RLS d'origine
            DB::statement("SET app.client_id = '{$originalClientId}'");
        }

        return response()->json(['message' => 'Licence supprimée avec succès.'], 200);
    }
}
