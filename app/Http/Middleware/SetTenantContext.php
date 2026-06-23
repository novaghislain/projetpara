<?php
// =============================================================================
// FICHIER : SetTenantContext.php
// RÔLE    : Middleware — Injecte le client_id dans la session BDD (RLS)
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Ce middleware prépare le contexte multi-tenant au niveau base de données.
// Fonctionnement :
//   - PostgreSQL : exécute SET app.client_id = '<id>' pour le Row-Level Security
//   - MySQL/SQLite : ignoré (pas de RLS natif)
//
// Cela garantit que les requêtes BDD sont automatiquement filtrées
// par l'entreprise active de l'utilisateur connecté.
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Injecte le client_id de l'utilisateur authentifié
     * dans la session PostgreSQL pour le Row-Level Security (RLS).
     *
     * MySQL/SQLite : ignoré silencieusement.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ce middleware est conçu pour PostgreSQL (RLS).
        // MySQL ne supporte pas la syntaxe SET app.client_id — on saute.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $clientId = $user->client_id ?? 0;

            DB::statement("SET app.client_id = '{$clientId}'");
        } else {
            DB::statement("SET app.client_id = '0'");
        }

        return $next($request);
    }
}
