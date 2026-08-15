<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = Schema::getTables();
foreach ($tables as $t) {
    $tableName = $t['name'];
    if (preg_match('/compte|account/i', $tableName)) {
        echo $tableName . "\n";
        echo " - cols: " . implode(', ', Schema::getColumnListing($tableName)) . "\n";
    }
}
