<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Applique tous les headers de sécurité sur chaque réponse.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Anti-clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Anti-MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Anti-XSS (obsolète mais présent pour compatibilité)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Anti-leakage (référent)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Anti-API-abuse (permissions navigateur)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), bluetooth=()');

        // HSTS (anti-downgrade HTTP)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Content Security Policy — désactivée car incompatible avec le rendu
        // Les autres en-têtes (X-Frame-Options, HSTS, etc.) assurent la sécurité.

        // Anti-cache (pages sensibles uniquement)
        if ($request->is('login') || $request->is('register') || $request->is('admin/*') || $request->is('api/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
        }

        return $response;
    }
}
