<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureComptableIndependantAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isAutonomousAccountant()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès réservé aux comptables indépendants.'], 403);
            }
            abort(403, 'Accès réservé aux comptables indépendants.');
        }

        return $next($request);
    }
}
