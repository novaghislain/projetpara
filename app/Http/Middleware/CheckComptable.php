<?php
// =============================================================================
// FICHIER : CheckComptable.php
// RÔLE    : Middleware — Vérifie que l'utilisateur est un comptable du cabinet
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Protège les routes du module comptabilité réservées aux comptables GEL.
// Les comptables ont un accès transversal à toutes les entreprises clientes
// pour la saisie, la révision et la clôture des comptes.
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckComptable
{
    /**
     * Vérifie que l'utilisateur est un comptable du cabinet.
     * Sinon, retourne 401 ou 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(401, 'Non authentifié.');
        }

        if (!Auth::user()->isComptable()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès réservé aux comptables.'], 403);
            }
            abort(403, 'Accès réservé aux comptables.');
        }

        return $next($request);
    }
}
