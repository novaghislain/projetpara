<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantPermission
{
    /**
     * Vérifie que l'utilisateur a une permission spécifique dans son contexte tenant.
     *
     * Utilisation : ->middleware('tenant.permission:comptabilite.consulter')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Non authentifié.');
        }

        // Super-admin a toutes les permissions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier via Spatie
        if (! $user->hasPermissionTo($permission)) {
            abort(403, config('permission.messages.unauthorized', 'Action non autorisée.'));
        }

        // Vérifier que l'utilisateur ne franchit pas les limites tenant
        $cabinetId = session('current_cabinet_id');
        $routeCabinetId = $request->route('cabinet_id');

        if ($routeCabinetId && $cabinetId && (int) $routeCabinetId !== (int) $cabinetId) {
            abort(403, config('permission.messages.cross_tenant', 'Accès cross-tenant refusé.'));
        }

        return $next($request);
    }
}
