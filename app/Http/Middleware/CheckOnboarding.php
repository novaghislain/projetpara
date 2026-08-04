<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboarding
{
    /**
     * Vérifie que l'utilisateur a complété son profil (onboarding).
     * Si non, redirige vers la page de completion de profil.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Si l'utilisateur a déjà complété son onboarding, on passe
        if ($user->hasCompletedOnboarding()) {
            return $next($request);
        }

        // Éviter les boucles de redirection
        if ($request->routeIs('onboarding.*') || $request->is('onboarding/*')) {
            return $next($request);
        }

        // Rediriger vers la page de profil
        if ($user->onboarding_token) {
            return redirect()->route('onboarding.profil', ['token' => $user->onboarding_token]);
        }

        // Pas de token ? En générer un et rediriger
        $user->onboarding_token = \Illuminate\Support\Str::random(40);
        $user->save();

        return redirect()->route('onboarding.profil', ['token' => $user->onboarding_token]);
    }
}
