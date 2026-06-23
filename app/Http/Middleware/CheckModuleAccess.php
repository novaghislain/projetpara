<?php
// =============================================================================
// FICHIER : CheckModuleAccess.php
// RÔLE    : Middleware — Vérifie l'accès à un module spécifique
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Middleware paramétrable utilisé sur les routes protégées par module.
//
// Utilisation dans les routes :
//   Route::middleware('module:comptabilite')
//   Route::middleware('module:comptabilite,creer')
//
// Paramètres :
//   1. module (obligatoire) : le slug du module (ex: comptabilite, caisse, rh)
//   2. action (optionnelle) : permission spécifique (ex: creer, modifier, supprimer)
//
// Comportement :
//   - Super admin : toujours autorisé (bypass)
//   - Utilisateur normal : vérifié via User::hasModuleAccess() + User::canModule()
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Vérifie que l'utilisateur a accès au module spécifié.
     *
     * Paramètres : module (obligatoire), action (optionnelle)
     * Utilisation : Route::middleware('module:comptabilite')
     *               Route::middleware('module:comptabilite,creer')
     */
    public function handle(Request $request, Closure $next, string $module, ?string $action = null): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $user = Auth::user();

        // Super Admin a toujours accès
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier l'accès au module
        if (!$user->hasModuleAccess($module)) {
            return response()->json([
                'message' => 'Vous n\'avez pas accès au module « ' . $module . ' ».',
                'error' => 'module_forbidden',
                'module' => $module,
            ], 403);
        }

        // Si une action spécifique est demandée, vérifier aussi
        if ($action && !$user->canModule($module, $action)) {
            return response()->json([
                'message' => 'Vous n\'avez pas la permission « ' . $action . ' » dans le module « ' . $module . ' ».',
                'error' => 'action_forbidden',
                'module' => $module,
                'action' => $action,
            ], 403);
        }

        return $next($request);
    }
}
