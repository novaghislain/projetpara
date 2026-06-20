<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfClient
{
    /**
     * Redirige les clients vers leur portail s'ils tentent d'accéder
     * aux routes du cabinet ou de l'entreprise.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isClient()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès client uniquement.'], 403);
            }
            return redirect()->route('client.orders.index');
        }

        return $next($request);
    }
}
