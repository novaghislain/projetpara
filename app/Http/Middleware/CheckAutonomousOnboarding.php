<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAutonomousOnboarding
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
        $user = Auth::user();

        // Si l'utilisateur est un secrétaire indépendant (autonome)
        if ($user && $user->isAutonomousSecretary()) {
            
            // Les URLs à ignorer pour ne pas créer de boucle infinie
            $exceptUrls = [
                'gel-secretary/autonomous/enterprise/create',
                'gel-secretary/autonomous/enterprise',
                'logout',
                'gel-secretary/settings',
                'gel-secretary/settings/*'
            ];

            // Si on est déjà sur l'une des pages d'exception, on passe
            foreach ($exceptUrls as $url) {
                if ($request->is($url)) {
                    return $next($request);
                }
            }

            // S'il n'a pas encore créé son entreprise
            if ($user->userClients()->count() === 0) {
                return redirect()->route('gel-secretary.autonomous.enterprise.create')
                    ->with('warning', 'Vous devez créer votre espace entreprise avant de continuer.');
            }
        }

        return $next($request);
    }
}
