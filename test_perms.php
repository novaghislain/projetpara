<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@demo.com')->first();
$aff = $user->affectations()->first();

echo "Role: " . $aff->role->code . "\n";
echo "Role ID: " . $aff->role_id . "\n";

$permissions = DB::table('role_permission')
    ->where('role_id', $aff->role_id)
    ->where('ressource', 'fiscalite')
    ->get();

echo "Permissions for fiscalite on role:\n";
foreach($permissions as $p) {
    echo "- " . $p->action . ": " . ($p->autorise ? 'Yes' : 'No') . "\n";
}

$permissions = $aff->permissions()
    ->where('ressource', 'fiscalite')
    ->get();

echo "Permissions for fiscalite on affectation:\n";
foreach($permissions as $p) {
    echo "- " . $p->action . ": " . ($p->autorise ? 'Yes' : 'No') . "\n";
}
