<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Critical column checks based on errors we've seen
$checks = [
    ['utilisateurs', 'role'],
    ['utilisateurs', 'active_client_id'],
    ['utilisateurs', 'cabinet_id'],
    ['utilisateurs', 'is_autonomous'],
    ['clients', 'nom_entreprise'],
    ['clients', 'cabinet_id'],
    ['gel_conformite', 'date_expiration'],
    ['gel_conformite', 'obligation'],
    ['client_folders', 'deleted_at'],
    ['documents', 'deleted_at'],
    ['documents', 'is_archived'],
    ['documents', 'is_favorite'],
    ['documents', 'folder_id'],
    ['documents', 'is_secured'],
    ['documents', 'privacy_level'],
    ['documents', 'category'],
    ['documents', 'tags'],
    ['client_contacts', 'user_id'],
    ['dae_agenda_events', 'deleted_at'],
    ['reunions', 'deleted_at'],
    ['dae_meeting_minutes', 'deleted_at'],
    ['client_call_logs', 'deleted_at'],
    ['relance_rules', 'deleted_at'],
    ['dae_contrats', 'deleted_at'],
    ['gel_messages', 'deleted_at'],
    ['rh_employees', 'deleted_at'],
    ['reservations', 'deleted_at'],
    ['business_trips', 'deleted_at'],
    ['tasks', 'deleted_at'],
    ['courriers', 'deleted_at'],
    ['coordination_events', 'deleted_at'],
];

echo "=== Column Checks ===\n\n";
$missing = [];
foreach ($checks as [$table, $column]) {
    if (!Schema::hasTable($table)) {
        echo "NO TABLE: $table\n";
        continue;
    }
    if (!Schema::hasColumn($table, $column)) {
        echo "MISSING COLUMN: $table.$column\n";
        $missing[] = "$table.$column";
    }
}

if (empty($missing)) {
    echo "✓ All critical columns exist!\n";
} else {
    echo "\n" . count($missing) . " missing column(s)\n";
}
