<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionQuota
{
    /**
     * Handle an incoming request.
     * Vérifie si le cabinet a dépassé ses quotas d'abonnement (dossiers clients, factures, etc.)
     */
    public function handle(Request $request, Closure $next, string $resourceType = 'clients'): Response
    {
        $cabinet = auth()->user()->cabinet;

        if (!$cabinet) {
            return $next($request); // Pas applicable aux SuperAdmins
        }

        // Récupérer l'abonnement actif (simulé ici via des méthodes qui seraient sur le modèle Cabinet)
        $subscription = $cabinet->activeSubscription(); // ex: retourne un objet ou null
        
        // Logique de blocage
        if (!$subscription) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Aucun abonnement actif. Veuillez souscrire à un plan.'], 403);
            }
            return redirect()->route('cabinet.billing')->with('error', 'Aucun abonnement actif.');
        }

        // Vérification des quotas en fonction du type de ressource (ex: clients, factures)
        if ($resourceType === 'clients') {
            $maxClients = $subscription->plan->max_clients ?? 0; // 0 = illimité
            if ($maxClients > 0 && $cabinet->clients()->count() >= $maxClients) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Quota de dossiers clients atteint pour votre plan actuel.'], 403);
                }
                return redirect()->route('cabinet.billing')->with('error', 'Quota de dossiers atteint. Veuillez upgrader votre plan.');
            }
        }

        return $next($request);
    }
}
