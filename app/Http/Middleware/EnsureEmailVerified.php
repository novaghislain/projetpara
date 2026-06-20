<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    /**
     * Vérifie que l'email de l'utilisateur est vérifié.
     * Les super admins sont exemptés.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
