<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsSecretaire
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifie si l'utilisateur est authentifié et a le rôle de secrétaire
        if (Auth::check() && Auth::user()->isSecretaire()) {
            return $next($request);
        }

        // Sinon, redirection vers la page de connexion ou dashboard général avec une erreur
        return redirect()->route('login')->with('error', 'Accès refusé. Cette section est réservée aux secrétaires.');
    }
}
