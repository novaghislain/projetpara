<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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

Schedule::job(new \App\Jobs\DailyDigestJob)->dailyAt('07:00');
Schedule::job(new \App\Jobs\TaskEscalationJob)->hourly();
Schedule::job(new \App\Jobs\ContactBirthdayJob)->dailyAt('08:00');
