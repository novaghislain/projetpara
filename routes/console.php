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
