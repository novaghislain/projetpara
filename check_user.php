<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::first();
echo "Role: " . ($u->role ?? 'null') . "\n";
echo "Role secretaire: " . ($u->role_secretaire ? 'true' : 'false') . "\n";
echo "Cabinet ID: " . ($u->cabinet_id ?? 'null') . "\n";
echo "Client ID: " . ($u->client_id ?? 'null') . "\n";
echo "Autonomous: " . ($u->isAutonomousSecretary() ? 'true' : 'false') . "\n";
