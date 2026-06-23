<?php
// =============================================================================
// FICHIER : EnsureNotClient.php
// RÔLE    : Middleware — Bloque les clients publics du parcours e-commerce
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Similaire à RedirectIfClient mais avec un message différent.
// Utilisé sur les routes strictement internes où un client n'a rien à faire.
//
// Les clients publics (role=client) sont ceux qui commandent sur le site
// e-commerce — ils n'ont qu'un accès à /mes-commandes.
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotClient
{
    /**
     * Bloque les clients publics (role=client) d'accéder aux routes du cabinet GEL.
     * Les clients ne voient QUE leur espace /mes-commandes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'client') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Accès réservé. Votre espace est sur /mes-commandes.'
                ], 403);
            }
            // Redirige le client vers son espace personnel
            return redirect()->route('client.orders.index');
        }

        return $next($request);
    }
}
