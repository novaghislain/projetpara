<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthenticatedSessionController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Enregistrer la dernière connexion
        $user->update(['last_login_at' => now()]);

        // Si une commande est en attente (client venant du catalogue), on le redirige vers le wizard
        if (session('order_service_id')) {
            return redirect()->route('commande.step');
        }

        // Rediriger les utilisateurs d'entreprise vers leur portail
        if ($user->client_id) {
            return redirect()->intended(route('company.dashboard'));
        }

        // Clients purs (role=client) -> espace client
        if ($user->role === 'client') {
            return redirect()->intended(route('client.orders.index'));
        }

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
