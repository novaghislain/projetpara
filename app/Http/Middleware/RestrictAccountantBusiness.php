<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAccountantBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $isAccountant = ($user && method_exists($user, 'isAccountant') && $user->isAccountant()) || session('current_client_id');
        if ($isAccountant) {
            abort(403, 'Accès refusé. Les comptables ne peuvent pas modifier les paramètres de l\'entreprise.');
        }

        return $next($request);
    }
}
