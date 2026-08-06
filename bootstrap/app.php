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
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Routage de l'Espace Client Externe
            \Illuminate\Support\Facades\Route::middleware('web')
                ->domain(env('PORTAL_DOMAIN', 'client.gelsabinet.com'))
                ->group(base_path('routes/gel-client.php'));
                
            // Routage du Portail Administrateur
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/gel-admin.php'));
                
            // Routage du Portail Super Administrateur (Niveau 2)
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/gel-super-admin.php'));
        },
    )
    ->withSchedule(function (Schedule $schedule): void {
        // ─── Tâches planifiées (CRON) ──────────────────────────────────
        // Envoi automatique des relances clients chaque jour à 09h00
        $schedule->command('relance:send')->dailyAt('09:00');
        // Vérification des alertes d'actifs IT chaque lundi à 08h00
        $schedule->command('it:asset-alerts')->weeklyOn(1, '08:00');
        // Sauvegarde automatique de la base de données chaque jour à 02h00
        $schedule->command('db:backup --compress')->dailyAt('02:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // ─── Middleware global (appliqué à TOUTES les requêtes) ────────
        // LogRedirects : enregistre les redirections HTTP pour débogage
        $middleware->append(\App\Http\Middleware\LogRedirects::class);
        // SecurityHeaders : en-têtes de sécurité (CSP, HSTS, X-Frame-Options...)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // AuditMiddleware : enregistre toutes les mutations dans audit_logs
        $middleware->append(\App\Http\Middleware\AuditMiddleware::class);

        // ─── Exclusion CSRF (routes d'API qui utilisent le middleware 'web' pour la session) ─
        $middleware->validateCsrfTokens(except: [
            'api/login',
            'api/auth/login',
        ]);

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
            'gel.secretaire' => \App\Http\Middleware\IsSecretaire::class,
            'ensure.company' => \App\Http\Middleware\EnsureCompanyAccess::class,
            'verified'       => \App\Http\Middleware\EnsureEmailVerified::class,
            'not_suspended'  => \App\Http\Middleware\CheckNotSuspended::class,
            'can.action'     => \App\Http\Middleware\CheckActionPermission::class,
            'redirect.client'=> \App\Http\Middleware\RedirectIfClient::class,
            // Sécurité — limitation de débit
            'throttle.api'    => \App\Http\Middleware\ApiRateLimiter::class,
            'throttle.login'  => \App\Http\Middleware\LoginThrottle::class,
            // Comptabilité par domaine d'activité
            'compta.domain'   => \App\Http\Middleware\CheckComptaDomainModule::class,
            'tenant'          => \App\Http\Middleware\TenantMiddleware::class,
            // ACL multi-tenant (Spatie Permission)
            'tenant.resolve'  => \App\Http\Middleware\TenantResolver::class,
            'tenant.permission' => \App\Http\Middleware\CheckTenantPermission::class,
            // Spatie Permission — middleware intégré
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            // Onboarding GEL
            'onboarding'       => \App\Http\Middleware\CheckOnboarding::class,
            'enterprise.owner' => \App\Http\Middleware\CheckEnterpriseOwner::class,
            // Custom for accountants
            'check.client.access' => \App\Http\Middleware\CheckClientAccess::class,
            // Admin Cabinet (Espace Administrateur)
            'admin.cabinet'    => \App\Http\Middleware\AdminCabinetMiddleware::class,
            // Super Administrateur (Niveau 2)
            'super_admin'      => \App\Http\Middleware\SuperAdminMiddleware::class,
            // Restriction IP Client B2B
            'restrict.client.ip' => \App\Http\Middleware\RestrictClientIp::class,
        ]);

        // ─── Middleware applicatif (exécuté après les globaux) ─────────
        // SetTenantContext : injecte le client_id dans la session de BDD
        // pour le Row-Level Security (PostgreSQL uniquement).
        $middleware->append(\App\Http\Middleware\SetTenantContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
