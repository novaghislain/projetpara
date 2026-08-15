<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = Schema::getTables();
foreach ($tables as $tableInfo) {
    $table = $tableInfo['name'];
    if (strpos($table, 'journal') !== false || strpos($table, 'account') !== false || strpos($table, 'ecriture') !== false || strpos($table, 'plan') !== false) {
        echo $table . "\n";
        $columns = Schema::getColumnListing($table);
        echo "  " . implode(', ', $columns) . "\n";
    }
}
