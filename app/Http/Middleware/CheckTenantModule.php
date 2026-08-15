<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Client;

class CheckTenantModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $moduleName
     */
    public function handle(Request $request, Closure $next, string $moduleName): Response
    {
        // On récupère le client_id depuis la requête, la session, ou l'utilisateur connecté
        $clientId = $request->header('X-Client-Id') ?? $request->input('client_id');
        
        if (!$clientId) {
            // S'il n'y a pas de client_id explicite, on laisse passer ou on gère selon l'architecture
            // Pour l'instant, on laisse passer si ce n'est pas identifié.
            return $next($request);
        }

        $client = Client::find($clientId);

        if (!$client) {
            return response()->json(['error' => 'Client (Tenant) introuvable.'], 404);
        }

        $activeModules = $client->active_modules ?? [];

        if (!in_array($moduleName, $activeModules)) {
            return response()->json([
                'error' => 'Module non activé',
                'message' => "Le module '{$moduleName}' n'est pas inclus dans l'abonnement de votre secteur d'activité."
            ], 403);
        }

        return $next($request);
    }
}
