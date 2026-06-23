<?php
// =============================================================================
// FICHIER : CheckComptaDomainModule.php
// RÔLE    : Middleware — Vérifie qu'un module comptable métier est actif
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Middleware pour la comptabilité par domaine d'activité.
// Chaque entreprise cliente a un domaine (commerce, hotel, scolaire, etc.)
// qui détermine les modules comptables disponibles (stock, chambres, etc.).
//
// Ce middleware vérifie que le module métier demandé est activé pour le client
// connecté avant d'autoriser l'accès à la route.
//
// Utilisation dans les routes :
//   Route::middleware('compta.domain:stock')
//   Route::middleware('compta.domain:gestion_chambres')
//
// Bypass : super admin, comptables GEL et admins généraux.
// =============================================================================

namespace App\Http\Middleware;

use App\Services\TenantDomainService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckComptaDomainModule
{
    /**
     * Vérifie que le module comptable métier demandé est actif pour ce client.
     *
     * @param  string  $module  Le module à vérifier (ex: 'stock', 'gestion_chambres')
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = Auth::user();

        // Super admin et comptables GEL passent toujours
        if ($user->is_super_admin || $user->is_comptable || $user->is_admin) {
            return $next($request);
        }

        // L'utilisateur doit avoir un client_id actif
        $clientId = $user->active_client_id ?? $user->client_id;
        if (!$clientId) {
            abort(403, 'Aucune entreprise associée.');
        }

        // Vérifie que ce module métier est actif pour ce client
        if (!TenantDomainService::hasComptaModule($clientId, $module)) {
            abort(403, "Le module [{$module}] n'est pas disponible pour votre domaine d'activité.");
        }

        return $next($request);
    }
}
