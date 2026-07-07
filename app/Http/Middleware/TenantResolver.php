<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantResolver
{
    /**
     * Résout et définit le contexte tenant (cabinet_id) pour la requête.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $cabinetId = $this->resolveCabinetId($user);

            if ($cabinetId) {
                // Partager avec l'application
                view()->share('currentCabinetId', $cabinetId);
                config(['app.current_cabinet_id' => $cabinetId]);

                // Injecter dans la session pour les requêtes BDD
                session()->put('current_cabinet_id', $cabinetId);

                // Pour Spatie Permission (team_foreign_key)
                if (method_exists($user, 'setPermissionsTeamId')) {
                    $user->setPermissionsTeamId($cabinetId);
                }
            }
        }

        return $next($request);
    }

    private function resolveCabinetId($user): ?int
    {
        if ($user->cabinet_id) {
            return (int) $user->cabinet_id;
        }

        if ($user->client_id && $user->client && $user->client->company && $user->client->company->cabinet_id) {
            return (int) $user->client->company->cabinet_id;
        }

        if ($user->active_client_id) {
            return (int) $user->active_client_id;
        }

        return null;
    }
}
