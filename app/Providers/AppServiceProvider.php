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
    }
}
