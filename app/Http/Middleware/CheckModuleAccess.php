<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $module
     * @param  string  $action
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'consulter'): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Non authentifié.');
        }

        // Dans le portail expert (gel-accountant), le clientId peut être nul ou ne pas
        // correspondre à l'entreprise du cabinet. On va chercher l'entreprise (cabinet)
        // dans les affectations si le hasPermissionTo classique échoue.
        $clientId = session('active_client_id') ?? session('current_client_id');
        $entrepriseId = session('active_entreprise_id');

        // Tenter de vérifier avec entrepriseId, ou clientId
        $contextId = $entrepriseId ?? $clientId;

        \Log::info("CheckModuleAccess Debug", [
            'user' => $user->id,
            'email' => $user->email,
            'module' => $module,
            'action' => $action,
            'clientId' => $clientId,
            'entrepriseId' => $entrepriseId,
            'contextId' => $contextId,
        ]);

        if (!$user->hasPermissionTo($module, $action, $contextId)) {
            // Tentative sur l'entreprise du cabinet (la première affectation active)
            $cabinetId = $user->affectations()->whereIn('statut', ['actif', 'active'])->first()?->entreprise_id;
            
            \Log::info("Fallback cabinetId", ['cabinetId' => $cabinetId]);

            if (!$cabinetId || !$user->hasPermissionTo($module, $action, $cabinetId)) {
                abort(403, "Vous n'avez pas la permission '{$action}' sur le module '{$module}'.");
            }
        }
        
        return $next($request);
    }
}
