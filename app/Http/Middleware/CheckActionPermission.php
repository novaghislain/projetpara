<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActionPermission
{
    /**
     * Vérifie que l'utilisateur a une permission spécifique (module:action).
     *
     * Usage: ->middleware('can.action:module,action')
     * Exemple: ->middleware('can.action:comptabilite,lire')
     */
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $user = Auth::user();

        // Super Admin a toujours accès
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Admin entreprise a accès à tout
        if ($user->isCompanyAdmin()) {
            return $next($request);
        }

        if (!$user->canModule($module, $action)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Permission refusée : ' . $module . ':' . $action,
                    'error' => 'permission_denied',
                    'module' => $module,
                    'action' => $action,
                ], 403);
            }
            abort(403, 'Action non autorisée.');
        }

        return $next($request);
    }
}
