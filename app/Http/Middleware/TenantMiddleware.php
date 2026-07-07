<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Vérifie que l'utilisateur authentifié a un tenant associé.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun tenant associé à cet utilisateur.',
            ], 403);
        }

        return $next($request);
    }
}
