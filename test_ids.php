<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@demo.com')->first();
$aff = $user->affectations()->first();

echo "Entreprise ID: " . $aff->entreprise_id . "\n";
echo "User's active_client_id: " . $user->active_client_id . "\n";

$client = \App\Models\Client::first();
echo "First Client ID: " . ($client ? $client->id : 'none') . "\n";
