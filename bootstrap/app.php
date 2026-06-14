<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
            'role'        => \App\Http\Middleware\CheckRole::class,
            'company'     => \App\Http\Middleware\CheckCompanyAccess::class,
            'company.auth'=> \App\Http\Middleware\EnsureIsCompanyAdmin::class,
            'module'      => \App\Http\Middleware\CheckModuleAccess::class,
            'not_client'  => \App\Http\Middleware\EnsureNotClient::class,
        ]);

        // RLS multi-tenant : injecte le client_id dans la session PostgreSQL
        $middleware->append(\App\Http\Middleware\SetTenantContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
