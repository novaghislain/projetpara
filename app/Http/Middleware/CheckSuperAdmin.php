<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Vérifie que l'utilisateur est un Super Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(401, 'Non authentifié.');
        }

        if (!Auth::user()->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès réservé aux Super Administrateurs.'], 403);
            }
            abort(403, 'Accès réservé aux Super Administrateurs.');
        }

        return $next($request);
    }
}
