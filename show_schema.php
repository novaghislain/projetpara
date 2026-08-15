<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $cols = DB::select('SHOW COLUMNS FROM gel_conformite');
    print_r(array_column($cols, 'Field'));
} catch (Exception $e) {
    echo $e->getMessage();
}
