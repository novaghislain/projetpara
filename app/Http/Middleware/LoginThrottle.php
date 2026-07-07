<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginThrottle
{
    protected $limiter;
    protected $maxAttempts = 5;
    protected $decayMinutes = 30;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next)
    {
        $key = 'login:' . Str::lower($request->input('email')) . '|' . $request->ip();

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);
            return back()->withErrors([
                'email' => "Compte verrouillé. Réessayez dans " . ceil($seconds / 60) . " minutes.",
            ])->onlyInput('email');
        }

        $response = $next($request);

        // Si la réponse est une redirection avec erreur (échec login), on compte la tentative
        if ($response->getStatusCode() === 302 && session('errors')?->has('email')) {
            $this->limiter->hit($key, $this->decayMinutes * 60);
        }

        return $response;
    }
}
