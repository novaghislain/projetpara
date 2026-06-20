<?php

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
     * dans la session PostgreSQL pour le RLS.
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
