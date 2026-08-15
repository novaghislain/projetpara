<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ─── Langue et date en Français ───────────────────────────────────
        \Carbon\Carbon::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        // ─── Redirection intelligente des utilisateurs authentifiés ──────
        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            return route('dashboard');
        });

        // Précharge les assets Vite (3 en parallèle) pour des pages plus rapides
        Vite::prefetch(concurrency: 3);

        // ─── Politique de mot de passe forte ─────────────────────────────
        Password::defaults(function () {
            return Password::min(8);
        });
    }
}
