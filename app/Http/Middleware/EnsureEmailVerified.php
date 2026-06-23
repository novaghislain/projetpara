<?php
// =============================================================================
// FICHIER : EnsureEmailVerified.php
// RÔLE    : Middleware — Vérifie que l'email de l'utilisateur est vérifié
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Actuellement, ce middleware est un passe-plat (toujours autorisé).
// À activer quand la vérification d'email sera obligatoire pour tous.
//
// Les super admins sont exemptés (toujours autorisés).
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    /**
     * Vérifie que l'email de l'utilisateur est vérifié.
     * Les super admins sont exemptés.
     *
     * @todo  Activer la vérification quand le flux d'inscription sera complet
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
