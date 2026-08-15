<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$gc = DB::table('gel_comptes_comptables')->first();
$gat = DB::table('gel_account_types')->first();
$aa = DB::table('accounting_accounts')->first();

echo "gel_comptes_comptables:\n";
print_r($gc);
echo "gel_account_types:\n";
print_r($gat);
echo "accounting_accounts:\n";
print_r($aa);
