<?php
// =============================================================================
// FICHIER : CheckNotSuspended.php
// RÔLE    : Middleware — Vérifie que l'utilisateur n'est pas suspendu
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Appliqué aux routes nécessitant un compte actif.
// Si l'utilisateur est suspendu (is_suspended = true) :
//   - Déconnexion forcée (Auth::logout)
//   - Message d'erreur expliquant la suspension
//   - Redirection vers la page de connexion
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckNotSuspended
{
    /**
     * Vérifie que l'utilisateur n'est pas suspendu.
     * Si suspendu : déconnexion + message explicatif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(401, 'Non authentifié.');
        }

        $user = Auth::user();

        if ($user->isSuspended()) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Votre compte a été suspendu. Veuillez contacter l\'administrateur.',
                    'reason' => $user->suspended_reason,
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte a été suspendu. Veuillez contacter l\'administrateur.',
            ]);
        }

        return $next($request);
    }
}
