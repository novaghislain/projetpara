<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAffectation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Si l'utilisateur est un super_admin au niveau global (affectation sans entreprise ou entreprise GEL)
        // on peut ajouter une logique spécifique, mais pour simplifier, on vérifie si l'utilisateur
        // a une affectation active dans la session.
        
        $activeEntrepriseId = session('active_entreprise_id');
        
        if (!$activeEntrepriseId) {
            // Si pas d'entreprise active, on le redirige vers le sélecteur d'entreprise (dashboard global)
            // sauf s'il est déjà sur une route globale.
            if (!$request->routeIs('dashboard', 'workspace.set')) {
                return redirect()->route('dashboard')->with('error', 'Veuillez sélectionner une entreprise.');
            }
            return $next($request);
        }

        // Vérifier si l'utilisateur a des affectations actives pour cette entreprise
        $affectations = $user->affectations()
                            ->where('entreprise_id', $activeEntrepriseId)
                            ->where('statut', 'active')
                            ->with('role')
                            ->get();

        if ($affectations->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Accès refusé à cette entreprise.');
        }

        // Si des rôles spécifiques sont requis, on vérifie
        $hasRole = false;
        if (!empty($roles)) {
            foreach ($affectations as $affectation) {
                if ($affectation->role && in_array($affectation->role->code, $roles)) {
                    $hasRole = true;
                    break;
                }
            }
            
            // Le company_admin a accès à TOUT ? Non, le CDC dit l'inverse. 
            // On enforce strictement le rôle.
            if (!$hasRole) {
                abort(403, 'Accès non autorisé pour votre rôle.');
            }
        }

        // Stocker la première affectation courante dans la requête (pour compatibilité avec l'ancien code)
        // et la liste complète
        $request->attributes->set('current_affectation', $affectations->first());
        $request->attributes->set('user_affectations', $affectations);

        return $next($request);
    }
}
