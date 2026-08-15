<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

// Map: page → table actually used
$pages = [
    'dashboard'                 => ['utilisateurs', 'clients', 'audit_trails'],
    'clients'                   => ['clients', 'client_requests'],
    'conformite'                => ['gel_conformite', 'clients'],
    'tasks'                     => ['tasks'],
    'documents'                 => ['client_folders', 'documents'],
    'contacts'                  => ['client_contacts'],
    'services/hr'               => ['rh_employees', 'rh_leave_requests'],
    'reunions'                  => ['reunions'],
    'pv'                        => ['dae_meeting_minutes'],
    'services/reservations'     => ['reservations'],
    'services/business-trips'   => ['business_trips'],
    'courriers'                 => ['courriers'],
    'messagerie'                => ['gel_messages'],
    'relances'                  => ['relance_rules'],
    'calls'                     => ['client_call_logs'],
    'contrats'                  => ['dae_contrats'],
    'safebox'                   => ['documents'],
    'coordination'              => ['coordination_events'],
    'historique'                => ['audit_trails'],
    'notifications'             => ['notifications'],
    'settings'                  => ['utilisateurs'],
];

echo "=== Page → Table Check ===\n\n";
$missing = [];
foreach ($pages as $page => $tables) {
    foreach ($tables as $table) {
        $exists = Schema::hasTable($table);
        if (!$exists) {
            echo "MISSING  /gel-secretary/$page → $table\n";
            $missing[] = $table;
        }
    }
}

if (empty($missing)) {
    echo "✓ All tables exist!\n";
}
echo "\nMissing: " . implode(', ', array_unique($missing)) . "\n";
