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
            $user = Auth::user();
            
            // Redirection pour le secrétaire indépendant qui n'a pas encore configuré son entreprise
            if ($user->isAutonomousSecretary() && $user->userClients()->count() === 0) {
                $currentRoute = $request->route()->getName();
                $allowedRoutes = [
                    'gel-secretary.autonomous.enterprise.create',
                    'gel-secretary.autonomous.enterprise.store',
                    'logout'
                ];
                
                if (!in_array($currentRoute, $allowedRoutes)) {
                    return redirect()->route('gel-secretary.autonomous.enterprise.create');
                }
            } elseif ($user->isAutonomousSecretary()) {
                // S'il a déjà une entreprise, on s'assure qu'elle est toujours le contexte actif
                if (!session()->has('active_client_id')) {
                    $userClient = $user->userClients()->first();
                    if ($userClient) {
                        // Utilise App\Models\Client (table: clients) car c'est ce que référence user_clients.client_id
                        session(['active_client_id' => $userClient->client_id]);
                        $user->update(['active_client_id' => $userClient->client_id]);
                    }
                }
            }

            return $next($request);
        }

        // Sinon, redirection vers la page de connexion ou dashboard général avec une erreur
        return redirect()->route('login')->with('error', 'Accès refusé. Cette section est réservée aux secrétaires.');
    }
}
