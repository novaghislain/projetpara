<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictClientIp
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->client_id) {
            $client = \App\Models\Client::find($user->client_id);
            
            if ($client && !empty($client->allowed_ips)) {
                $clientIp = $request->ip();
                
                if (!in_array($clientIp, $client->allowed_ips)) {
                    abort(403, 'Accès refusé. Votre adresse IP ('.$clientIp.') n\'est pas autorisée.');
                }
            }
        }

        return $next($request);
    }
}
