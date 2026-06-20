<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAccess
{
    /**
     * Vérifie que l'utilisateur a un contexte d'entreprise actif valide.
     * L'active_client_id doit exister dans user_clients.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(401, 'Non authentifié.');
        }

        $user = Auth::user();

        // Les super admins et comptables n'ont pas besoin de contexte entreprise
        if ($user->isSuperAdmin() || $user->isComptable() || $user->isClient()) {
            return $next($request);
        }

        $clientId = $user->active_client_id ?? $user->client_id;

        if (!$clientId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Aucune entreprise associée à votre compte.'], 403);
            }
            return redirect()->route('select.context');
        }

        // Vérifier que l'utilisateur est bien rattaché à cette entreprise
        $exists = \App\Models\UserClient::where('user_id', $user->id)
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->exists();

        if (!$exists) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Entreprise non accessible.'], 403);
            }
            return redirect()->route('select.context');
        }

        return $next($request);
    }
}
