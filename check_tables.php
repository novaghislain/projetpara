<?php
/**
 * Scan all tables needed by GelSecretary controllers and report missing ones.
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$needed = [
    // Core
    'utilisateurs', 'clients', 'gel_conformite', 'client_folders', 'documents',
    'client_contacts',
    // RH
    'rh_employees', 'rh_leave_requests', 'rh_contracts', 'rh_payrolls', 'rh_attendance', 'rh_expenses', 'rh_trainings', 'rh_alerts',
    // Tasks / PV / Meetings
    'tasks', 'meeting_minutes', 'reunions',
    // Services
    'reservations', 'business_trips',
    // Communication
    'courriers', 'messagerie', 'relances', 'call_logs',
    // Documents / Contrats
    'contrats',
    // Agenda
    'dae_agenda_events',
    // Audit / History
    'audit_trails',
    // Notifications
    'notifications',
    // Settings / Users
    'user_clients',
    // Client Requests
    'client_requests',
];

echo "=== Table Existence Check ===\n\n";
$missing = [];
foreach ($needed as $table) {
    $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
    if (!$exists) {
        echo "MISSING: $table\n";
        $missing[] = $table;
    } else {
        echo "OK:      $table\n";
    }
}

echo "\n=== Summary ===\n";
echo count($missing) . " table(s) missing: " . implode(', ', $missing) . "\n";
