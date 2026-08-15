<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::all();
foreach ($users as $u) {
    $u->mot_de_passe_hash = \Illuminate\Support\Facades\Hash::make('password123');
    $u->password = \Illuminate\Support\Facades\Hash::make('password123');
    $u->save();
}
echo "All passwords reset to password123.\n";
