<?php
// =============================================================================
// FICHIER : CheckSuperAdmin.php
// RÔLE    : Middleware — Vérifie que l'utilisateur est un Super Admin
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Utilisé pour protéger les routes réservées aux super administrateurs
// du cabinet GEL (paramétrage global, utilisateurs, plans, etc.).
//
// Différence avec AdminMiddleware :
//   - AdminMiddleware vérifie is_admin = true (moins restrictif)
//   - CheckSuperAdmin vérifie isSuperAdmin() (le rôle exact)
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Vérifie que l'utilisateur est un Super Admin.
     * Sinon, retourne 401 (non auth) ou 403 (accès refusé).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(401, 'Non authentifié.');
        }

        if (!Auth::user()->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès réservé aux Super Administrateurs.'], 403);
            }
            abort(403, 'Accès réservé aux Super Administrateurs.');
        }

        // 2FA Obligatoire sans exception pour le Super Admin
        if (empty(Auth::user()->two_factor_secret) || empty(Auth::user()->two_factor_confirmed_at)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'L\'authentification à double facteur (2FA) est strictement obligatoire pour cet accès.'], 403);
            }
            // Idéalement rediriger vers la page de profil pour l'activation
            return redirect()->route('profile.show')->with('error', 'Vous devez activer l\'authentification à deux facteurs (2FA) pour accéder au portail Super Admin.');
        }

        return $next($request);
    }
}
