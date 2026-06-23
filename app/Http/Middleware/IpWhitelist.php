<?php
// =============================================================================
// FICHIER : IpWhitelist.php
// RÔLE    : Middleware — Restreint l'accès par adresse IP
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Ce middleware est utilisé par les entreprises clientes qui souhaitent
// limiter l'accès à leur portail depuis des IP spécifiques.
//
// Fonctionnement :
//   - Si le client a défini allowed_ips dans sa fiche, seules ces IP sont
//     autorisées à accéder aux routes protégées.
//   - Si allowed_ips est vide ou non défini, aucune restriction.
//
// Utilisation dans les routes :
//   Route::middleware('ip.whitelist')->group(...)
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Vérifier que l'IP du client est autorisée.
     * Si le client a défini des IP autorisées (allowed_ips),
     * toute autre IP est bloquée.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = session('active_client_id') ?? auth()->user()?->client_id;

        if ($clientId) {
            $client = \App\Models\Client::find($clientId);

            if ($client && $client->allowed_ips) {
                $allowedIps = is_array($client->allowed_ips)
                    ? $client->allowed_ips
                    : json_decode($client->allowed_ips, true);

                if (is_array($allowedIps) && count($allowedIps) > 0) {
                    if (!in_array($request->ip(), $allowedIps)) {
                        abort(403, 'Accès refusé depuis cette adresse IP.');
                    }
                }
            }
        }

        return $next($request);
    }
}
