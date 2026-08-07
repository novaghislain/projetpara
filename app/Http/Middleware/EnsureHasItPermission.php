<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureHasItPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $permission
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Non authentifié.');
        }

        // Vérification de base : Est-ce un informaticien ou un Super Admin ?
        $isIt = $user->hasRole('informaticien') || $user->account_type === 'informaticien';
        $isSuperAdmin = $user->isSuperAdmin();

        if (!$isIt && !$isSuperAdmin) {
            abort(403, 'Accès réservé au pôle informatique GEL SABINET.');
        }

        // Si une permission spécifique est requise (ex: it.tickets.view)
        if ($permission && !$isSuperAdmin) {
            if (!$user->hasPermissionTo($permission)) {
                abort(403, "Vous n'avez pas la permission requise ($permission) pour accéder à cette ressource.");
            }
        }

        return $next($request);
    }
}
