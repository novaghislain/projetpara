<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Alias CDC §13.4 : relance:send
Artisan::command('relances:process', function () {
    $this->call('relance:send');
})->purpose('ProcessRelances — Relances automatiques clients impayés (CDC 13.4)');

// Agent OHADA — classe auto-découverte dans app/Console/Commands
// Utilisation : php artisan ai:agent-ohada --client-id=1

// Agent Customer — classe auto-découverte dans app/Console/Commands
// Utilisation : php artisan ai:agent-customer --client-id=1

// Agent Finance — classe auto-découverte dans app/Console/Commands
// Utilisation : php artisan ai:agent-finance --client-id=1 --fiscal-year-id=1
