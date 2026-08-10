<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckComptableSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Ce middleware ne s'applique qu'aux comptables indépendants
        if ($user->isAutonomousAccountant()) {
            
            // Si l'abonnement est explicitement bloqué, ou si l'essai est expiré
            $isBlocked = $user->subscription_status === 'blocked';
            $isTrialExpired = $user->subscription_status === 'trial' && $user->trial_ends_at && $user->trial_ends_at->isPast();

            if ($isBlocked || $isTrialExpired) {
                // Autoriser l'accès aux routes de gestion d'abonnement et de déconnexion
                if ($request->routeIs('gel-accountant.independant.subscription.*') || $request->routeIs('logout')) {
                    return $next($request);
                }

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Votre abonnement a expiré.'], 403);
                }

                return redirect()->route('gel-accountant.independant.subscription.expired');
            }
        }

        return $next($request);
    }
}
