<?php
// =============================================================================
// FICHIER : bootstrap/app.php
// RÔLE    : Configuration centrale de l'application Laravel
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Ce fichier est le point d'entrée de la configuration applicative.
// Il définit :
//   1. Les routes (web, console, health check)
//   2. Les tâches planifiées (CRON)
//   3. Les middlewares (globaux, alias, groupés)
//   4. La gestion des exceptions
//
// ⚠️  Tout middleware ajouté ici est chargé à chaque requête.
//     Les alias permettent d'utiliser ->middleware('nom') dans les routes.
// =============================================================================

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // ─── Tâches planifiées (CRON) ──────────────────────────────────
        // Envoi automatique des relances clients chaque jour à 09h00
        $schedule->command('relance:send')->dailyAt('09:00');
        // Vérification des alertes d'actifs IT chaque lundi à 08h00
        $schedule->command('it:asset-alerts')->weeklyOn(1, '08:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // ─── Middleware global (appliqué à TOUTES les requêtes) ────────
        // LogRedirects : enregistre les redirections HTTP pour débogage
        $middleware->append(\App\Http\Middleware\LogRedirects::class);

        // ─── Alias de middlewares ──────────────────────────────────────
        // Chaque alias peut être utilisé dans les routes :
        //   Route::get(...)->middleware('alias')
        //
        // Conventions de nommage :
        //   - admin.*       → rôles et permissions
        //   - company.*     → périmètre multi-entreprise
        //   - gel.*         → équipe GEL Cabinet (interne)
        //   - dae.*         → module DAE (Digital Asset Exchange)
        //   - compta.*      → module comptabilité par domaine
        $middleware->alias([
            'admin'          => \App\Http\Middleware\AdminMiddleware::class,
            'role'           => \App\Http\Middleware\CheckRole::class,
            'company'        => \App\Http\Middleware\CheckCompanyAccess::class,
            'company.auth'   => \App\Http\Middleware\EnsureIsCompanyAdmin::class,
            'module'         => \App\Http\Middleware\CheckModuleAccess::class,
            'not_client'     => \App\Http\Middleware\EnsureNotClient::class,
            'dae.secretaire' => \App\Http\Middleware\DaeSecretaireAccess::class,
            'ip.whitelist'   => \App\Http\Middleware\IpWhitelist::class,
            // Middlewares multi-tenant (périmètre entreprise)
            'gel.admin'      => \App\Http\Middleware\CheckSuperAdmin::class,
            'gel.comptable'  => \App\Http\Middleware\CheckComptable::class,
            'ensure.company' => \App\Http\Middleware\EnsureCompanyAccess::class,
            'verified'       => \App\Http\Middleware\EnsureEmailVerified::class,
            'not_suspended'  => \App\Http\Middleware\CheckNotSuspended::class,
            'can.action'     => \App\Http\Middleware\CheckActionPermission::class,
            'redirect.client'=> \App\Http\Middleware\RedirectIfClient::class,
            // Comptabilité par domaine d'activité
            'compta.domain' => \App\Http\Middleware\CheckComptaDomainModule::class,
        ]);

        // ─── Middleware applicatif (exécuté après les globaux) ─────────
        // SetTenantContext : injecte le client_id dans la session de BDD
        // pour le Row-Level Security (PostgreSQL uniquement).
        $middleware->append(\App\Http\Middleware\SetTenantContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
