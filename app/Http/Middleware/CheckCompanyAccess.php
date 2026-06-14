<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyAccess
{
    /**
     * Empêche les company_admins d'accéder aux routes GEL Cabinet.
     * Ils sont redirigés vers leur portail entreprise.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_company_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès réservé au personnel du cabinet.'], 403);
            }
            return redirect()->route('company.dashboard');
        }

        return $next($request);
    }
}
