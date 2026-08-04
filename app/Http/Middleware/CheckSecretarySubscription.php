<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSecretarySubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // On ne s'applique qu'aux secrétaires individuels
        if ($user && $user->isAutonomousSecretary()) {
            // Si le statut est expressément "expired" ou si l'essai est terminé sans statut "active"
            if ($user->subscription_status === 'expired' || !$user->hasActiveSubscription()) {
                // Éviter une boucle infinie de redirections si on est déjà sur la page d'abonnement
                if (!$request->routeIs('gel-secretary.subscription.expired')) {
                    // Si l'essai vient juste d'expirer et n'a pas encore le statut 'expired'
                    if ($user->subscription_status === 'trial' && $user->trial_ends_at && $user->trial_ends_at->isPast()) {
                        $user->update(['subscription_status' => 'expired']);
                    }
                    return redirect()->route('gel-secretary.subscription.expired');
                }
            }
        }

        return $next($request);
    }
}
