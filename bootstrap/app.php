<?php

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
        $schedule->command('relance:send')->dailyAt('09:00');
        $schedule->command('it:asset-alerts')->weeklyOn(1, '08:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\LogRedirects::class);

        $middleware->alias([
            'admin'          => \App\Http\Middleware\AdminMiddleware::class,
            'role'           => \App\Http\Middleware\CheckRole::class,
            'company'        => \App\Http\Middleware\CheckCompanyAccess::class,
            'company.auth'   => \App\Http\Middleware\EnsureIsCompanyAdmin::class,
            'module'         => \App\Http\Middleware\CheckModuleAccess::class,
            'not_client'     => \App\Http\Middleware\EnsureNotClient::class,
            'dae.secretaire' => \App\Http\Middleware\DaeSecretaireAccess::class,
            'ip.whitelist'   => \App\Http\Middleware\IpWhitelist::class,
            // Nouveaux middlewares multi-tenant
            'gel.admin'      => \App\Http\Middleware\CheckSuperAdmin::class,
            'gel.comptable'  => \App\Http\Middleware\CheckComptable::class,
            'ensure.company' => \App\Http\Middleware\EnsureCompanyAccess::class,
            'verified'       => \App\Http\Middleware\EnsureEmailVerified::class,
            'not_suspended'  => \App\Http\Middleware\CheckNotSuspended::class,
            'can.action'     => \App\Http\Middleware\CheckActionPermission::class,
            'redirect.client'=> \App\Http\Middleware\RedirectIfClient::class,
        ]);

        // RLS multi-tenant : injecte le client_id dans la session PostgreSQL
        $middleware->append(\App\Http\Middleware\SetTenantContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
