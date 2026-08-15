<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\System\SaaSIndustry;
use App\Models\Client;

class TenantSubscriptionController extends Controller
{
    /**
     * Create a new industry template
     */
    public function createIndustry(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:saas_industries,name',
            'description' => 'nullable|string',
            'default_modules' => 'required|array'
        ]);

        $industry = SaaSIndustry::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $industry,
            'message' => 'Secteur d\'activité SaaS créé avec ses modules par défaut.'
        ]);
    }

    /**
     * Assign a tenant to an industry and activate default modules
     */
    public function assignIndustryToTenant(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);

        $request->validate([
            'industry_id' => 'required|exists:saas_industries,id'
        ]);

        $industry = SaaSIndustry::findOrFail($request->industry_id);

        $client->industry_id = $industry->id;
        // Fusionner ou écraser les modules ? Ici on écrase avec les modules par défaut du secteur
        $client->active_modules = $industry->default_modules;
        $client->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'client' => $client->nom_entreprise,
                'industry' => $industry->name,
                'active_modules' => $client->active_modules
            ],
            'message' => 'Le tenant a été associé au secteur et ses modules ont été activés.'
        ]);
    }

    /**
     * Enable or disable a specific module for a tenant (Add-on)
     */
    public function toggleTenantModule(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);

        $request->validate([
            'module' => 'required|string',
            'action' => 'required|in:enable,disable'
        ]);

        $modules = $client->active_modules ?? [];

        if ($request->action === 'enable') {
            if (!in_array($request->module, $modules)) {
                $modules[] = $request->module;
            }
        } else {
            $modules = array_filter($modules, fn($m) => $m !== $request->module);
        }

        $client->active_modules = array_values($modules);
        $client->save();

        return response()->json([
            'status' => 'success',
            'active_modules' => $client->active_modules,
            'message' => "Module {$request->module} " . ($request->action === 'enable' ? 'activé' : 'désactivé') . " pour ce tenant."
        ]);
    }
}
