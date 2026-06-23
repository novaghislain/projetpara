<?php
// =============================================================================
// FICHIER : EnsureIsCompanyAdmin.php
// RÔLE    : Middleware — Vérifie que l'utilisateur est rattaché à une entreprise
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Protège les routes du portail entreprise.
// L'utilisateur doit avoir un client_id non nul (être rattaché à une entreprise
// cliente). Les utilisateurs internes du cabinet (super_admin, comptables, etc.)
// n'ont pas de client_id — ils sont redirigés vers leur dashboard.
//
// Note : ne distingue pas company_admin et employé standard.
//        Cette distinction se fait côté fonctionnalités dans les contrôleurs.
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsCompanyAdmin
{
    /**
     * Vérifie que l'utilisateur est rattaché à une entreprise (client_id).
     * Les utilisateurs du cabinet (super_admin, etc.) sont redirigés.
     * La distinction admin/utilisateur standard se fait côté fonctionnalités.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Tout utilisateur rattaché à une entreprise peut accéder au portail
            if (!$user->client_id) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Accès réservé aux utilisateurs d\'entreprise.'], 403);
                }
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
