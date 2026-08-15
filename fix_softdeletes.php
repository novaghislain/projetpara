<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Toutes les tables des modèles qui utilisent SoftDeletes
$tables = [
    'gel_tasks',
    'gel_documents',
    'gel_courriers',
    'gel_contacts',
    'dae_courriers',
    'gel_events',
    'gel_reunions',
    'gel_pv',
    'gel_relances',
    'gel_clients',
    'dae_contrats',
    'gel_calls',
    'gel_safebox',
    'gel_coordinations',
    'gel_conformites',
    'gel_reservations',
    'gel_business_trips',
    'gel_declarations',
    'gel_ventes',
    'gel_invitations',
];

foreach ($tables as $t) {
    try {
        $cols = array_column(DB::select("SHOW COLUMNS FROM `{$t}`"), 'Field');
        if (!in_array('deleted_at', $cols)) {
            DB::statement("ALTER TABLE `{$t}` ADD COLUMN `deleted_at` TIMESTAMP NULL");
            echo "✓ Added deleted_at → {$t}\n";
        } else {
            echo "  OK already exists → {$t}\n";
        }
    } catch (Exception $e) {
        echo "  SKIP {$t}: " . $e->getMessage() . "\n";
    }
}

echo "\nTerminé!\n";
