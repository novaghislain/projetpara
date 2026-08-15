<?php
/**
 * Inspect the fillable fields of models related to missing tables.
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$targets = [
    // meeting_minutes - look for Dae namespace
    'App\Models\Dae\DaeMeetingMinute',
    'App\Models\Dae\DaeReunion',
    'App\Models\Dae\DaeAgendaEvent',
    // Communication
    'App\Models\GelMessage',
    'App\Models\Gel\GelMessage',
    'App\Models\Relance',
    'App\Models\RelanceRule',
    'App\Models\ClientCallLog',
    // Documents
    'App\Models\Contrat',
    'App\Models\Courrier',
];

foreach ($targets as $class) {
    if (class_exists($class)) {
        try {
            $instance = new $class();
            $table = $instance->getTable();
            $fillable = $instance->getFillable();
            echo "CLASS: $class\n";
            echo "TABLE: $table\n";
            echo "FILLABLE: " . implode(', ', $fillable) . "\n\n";
        } catch (\Exception $e) {
            echo "CLASS: $class => ERROR: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "NOT FOUND: $class\n\n";
    }
}
