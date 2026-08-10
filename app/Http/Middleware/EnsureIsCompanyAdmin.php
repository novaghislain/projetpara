<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsCompanyAdmin
{
    /**
     * Verifie que l'utilisateur est rattache a une entreprise (client_id).
     * Les utilisateurs du cabinet (super_admin, etc.) sont rediriges.
     * La distinction admin/utilisateur standard se fait cote fonctionnalites.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Tout utilisateur rattache a une entreprise peut acceder au portail
            if (!$user->client_id) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Acces reserve aux utilisateurs d\'entreprise.'], 403);
                }
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
