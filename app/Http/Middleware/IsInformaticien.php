<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsInformaticien
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->account_type === 'informaticien') {
            return $next($request);
        }

        abort(403, 'Accès réservé au pôle informatique GEL SABINET.');
    }
}
