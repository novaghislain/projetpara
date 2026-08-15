<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking all tables for deleted_at...\n";

$tables = DB::select("SHOW TABLES");
$dbName = "Tables_in_gel_cabinet"; // Adjust if different

foreach ($tables as $t) {
    $tableArray = (array)$t;
    $tableName = array_values($tableArray)[0];
    
    try {
        $cols = Schema::getColumnListing($tableName);
        
        // Let's see if we should add deleted_at. 
        // We will add it to any table that sounds like a data model and doesn't have it, 
        // to be absolutely safe (it doesn't hurt to have a deleted_at column even if not used)
        if (!in_array('deleted_at', $cols)) {
            DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `deleted_at` TIMESTAMP NULL");
            echo "Added deleted_at to {$tableName}\n";
        }
    } catch (\Exception $e) {
        echo "Error on {$tableName}: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
