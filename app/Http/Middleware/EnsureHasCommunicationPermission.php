<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasCommunicationPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isCommunication()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Accès refusé. Réservé au pôle Communication.'], 403);
            }
            return redirect('/')->with('error', 'Accès refusé. Espace réservé au pôle Communication.');
        }

        return $next($request);
    }
}
