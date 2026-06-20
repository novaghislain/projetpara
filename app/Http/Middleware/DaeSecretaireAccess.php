<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DaeSecretaireAccess
{
    /**
     * Vérifie que l'utilisateur est un secrétaire DAE.
     * L'utilisateur doit avoir role_secretaire = true ET l'accès au module DAE.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $user = Auth::user();

        // Super admin a toujours accès
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier le statut secrétaire
        if (!$user->role_secretaire) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès réservé aux secrétaires DAE.'], 403);
            }
            abort(403, 'Accès réservé aux secrétaires DAE.');
        }

        // Vérifier l'accès au module DAE
        if (!$user->hasModuleAccess('dae')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Module DAE non accessible.'], 403);
            }
            abort(403, 'Module DAE non accessible.');
        }

        return $next($request);
    }
}
