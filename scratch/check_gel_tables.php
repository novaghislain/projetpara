<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = ['gel_ecritures', 'gel_journaux', 'gel_lignes_ecriture', 'gel_account_types'];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        $cols = Schema::getColumnListing($table);
        echo "$table: " . implode(', ', $cols) . "\n";
    } else {
        echo "$table: MISSING\n";
    }
}
