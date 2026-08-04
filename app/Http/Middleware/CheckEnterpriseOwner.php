<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEnterpriseOwner
{
    /**
     * Vérifie que l'utilisateur est bien le propriétaire de l'entreprise.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Vérifie que le user est bien un propriétaire d'entreprise
        if (!$user->estProprietaireEntreprise()) {
            // Les comptables et super-admins peuvent accéder
            if ($user->isComptable() || $user->isSuperAdmin()) {
                return $next($request);
            }

            abort(403, 'Accès réservé aux propriétaires d\'entreprise.');
        }

        return $next($request);
    }
}
