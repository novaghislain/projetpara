<?php
// =============================================================================
// FICHIER : AppServiceProvider.php
// RÔLE    : Bootstrapper principal — enregistre les services de l'application
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Ce provider est chargé à chaque requête. Il contient :
//   1. Le préchargement des assets Vite (pages plus rapides)
//   2. La politique de mot de passe forte globale
// =============================================================================

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services dans le conteneur.
     * Exécuté avant chaque requête.
     */
    public function register(): void
    {
        //
    }

    /**
     * Démarre les services après tout enregistrement.
     * Exécuté après register(), à chaque requête.
     */
    public function boot(): void
    {
        // Enregistrement des Observers
        \App\Models\Dae\DaeAgendaEvent::observe(\App\Observers\AgendaEventObserver::class);
        \App\Models\Client::observe(\App\Observers\ClientObserver::class);

        // ─── Redirection intelligente des utilisateurs authentifiés ──────
        // Lorsqu'un utilisateur déjà connecté visite /login, le middleware
        // 'guest' (RedirectIfAuthenticated) intercepte. On personnalise
        // la destination pour envoyer les comptables vers le bon dashboard.
        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            $user = Auth::user();

            // Comptable → nouveau GEL Accountant (Blade)
            if ($user && ($user->isComptable() || $user->role === 'comptable')) {
                return route('gel-accountant.dashboard');
            }

            // Super Admin → GEL dashboard
            if ($user && $user->isSuperAdmin()) {
                return route('dashboard');
            }

            // Company admin / manager → GEL Business dashboard
            if ($user && ($user->isCompanyAdmin() || $user->role === 'company_admin')) {
                return route('gel-business.dashboard');
            }

            // Par défaut
            return route('dashboard');
        });

        // Précharge les assets Vite (3 en parallèle) pour des pages plus rapides
        Vite::prefetch(concurrency: 3);

        // ─── Politique de mot de passe forte ─────────────────────────────
        // Appliquée automatiquement à TOUTES les routes de validation
        // qui utilisent Password::min(10) ou la règle 'password'.
        //
        // Exigences :
        //   - Minimum 10 caractères
        //   - Mixe minuscules / majuscules
        //   - Au moins une lettre
        //   - Au moins un chiffre
        //   - Au moins un symbole spécial
        //   - Vérification HaveIBeenPwned (mot de passe non compromis)
        // =================================================================
        Password::defaults(function () {
            return Password::min(10)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });

        // ─── Enregistrement des Observers ─────────────────────────────
        \App\Models\Gel\EcritureComptable::observe(\App\Observers\Gel\EcritureObserver::class);
    }
}
