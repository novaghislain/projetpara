<?php
// =============================================================================
// FICHIER : LogRedirects.php
// RÔLE    : Middleware — Enregistre les redirections dans les logs
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Middleware global qui se déclenche sur chaque requête.
// Actuellement, il est pass-through (ne fait rien).
//
// @todo  Implémenter le logging des redirections HTTP pour déboguer
//        les boucles de redirection en environnement multi-tenant.
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRedirects
{
    /**
     * Middleware pass-through — prêt pour l'implémentation du logging.
     *
     * @todo  Logger les redirections avec Request::path() et status code
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
