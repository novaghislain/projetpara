<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, $maxAttempts = 30, $decayMinutes = 1): Response
    {
        $key = 'api:' . ($request->user()?->id ?? $request->ip() . ':' . $request->path());

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);
            return response()->json([
                'error' => 'Trop de requêtes',
                'retry_after' => $seconds,
                'message' => "Veuillez réessayer dans {$seconds} secondes",
            ], Response::HTTP_TOO_MANY_REQUESTS)
                ->header('Retry-After', $seconds)
                ->header('X-RateLimit-Limit', $maxAttempts)
                ->header('X-RateLimit-Remaining', 0);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        $remaining = max(0, $maxAttempts - $this->limiter->attempts($key));
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);

        return $response;
    }
}
