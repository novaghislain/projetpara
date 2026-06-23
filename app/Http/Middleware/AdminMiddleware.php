<?php
// =============================================================================
// FICHIER : AdminMiddleware.php
// RÔLE    : Middleware — Vérifie que l'utilisateur est administrateur
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Middleware simple : vérifie le booléen is_admin sur l'utilisateur connecté.
// Utilisé pour protéger les routes d'administration sensibles.
//
// ⚠️  Ce middleware est moins restrictif que CheckSuperAdmin — il passe
//     dès que is_admin = true, quel que soit le rôle réel.
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Vérifie que l'utilisateur est administrateur (is_admin = true).
     * Sinon, retourne une erreur 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
