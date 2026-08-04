<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminCabinetMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if the user belongs to a cabinet, entreprise, or client
        if (!$user->cabinet_id && !$user->entreprise_id && !$user->client_id && !in_array($user->account_type, ['cabinet', 'entreprise', 'client'])) {
            abort(403, 'Accès réservé aux entreprises clientes.');
        }

        // Check if the user is the company admin/owner
        if (!$user->isCompanyAdmin() && !$user->hasRoleName('company_admin') && !$user->is_admin) {
            abort(403, 'Accès refusé. Vous devez être administrateur de l\'entreprise pour accéder à cet espace.');
        }

        return $next($request);
    }
}
