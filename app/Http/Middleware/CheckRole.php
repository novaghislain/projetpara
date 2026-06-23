<?php
// =============================================================================
// FICHIER : CheckRole.php
// RÔLE    : Middleware — Vérifie que l'utilisateur a un rôle spécifique
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Middleware paramétrable : on lui passe un ou plusieurs rôles acceptés.
//
// Utilisation dans les routes :
//   Route::middleware('role:super_admin')
//   Route::middleware('role:director,pole_responsible')
//
// Comportement :
//   - L'utilisateur doit avoir l'un des rôles spécifiés (OU logique)
//   - Si aucun rôle ne correspond → 403
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Vérifie que l'utilisateur authentifié a le rôle requis.
     * Usage: ->middleware('role:super_admin') ou 'role:director,pole_responsible'
     *
     * @param  string  ...$roles  Liste des rôles autorisés (OR)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            abort(401, 'Non authentifié.');
        }

        $user = Auth::user();

        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        abort(403, 'Accès non autorisé. Rôle requis: ' . implode(', ', $roles));
    }
}
