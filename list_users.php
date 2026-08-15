<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\User::all() as $u) {
    $role = $u->affectations->first()->role->code ?? 'none';
    echo "Email: " . $u->email . " | Role: " . $role . "\n";
}
