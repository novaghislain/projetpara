<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@demo.com')->first();
$aff = $user->affectations()->first();

echo "User Affectation Entreprise ID: " . $aff->entreprise_id . "\n";
