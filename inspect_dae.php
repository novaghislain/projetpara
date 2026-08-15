<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$classes = [
    'App\Models\Dae\DaeContrat',
    'App\Models\Dae\DaeMeetingMinute',
    'App\Models\Dae\DaeReunion',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        $m = new $class();
        echo "CLASS: $class\n";
        echo "TABLE: " . $m->getTable() . "\n";
        echo "FILLABLE: " . implode(', ', $m->getFillable()) . "\n\n";
    } else {
        echo "NOT FOUND: $class\n\n";
    }
}

// Also check Messagerie/Relance models
$class2 = [
    'App\Models\Gel\GelMessage',
    'App\Models\RelanceRule',
    'App\Models\ClientCallLog',
];
foreach ($class2 as $class) {
    if (class_exists($class)) {
        $m = new $class();
        echo "CLASS: $class\n";
        echo "TABLE: " . $m->getTable() . "\n";
        echo "FILLABLE: " . implode(', ', $m->getFillable()) . "\n\n";
    }
}
