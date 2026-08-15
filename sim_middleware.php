<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@demo.com')->first();
if(!$user) die("no user");

$module = 'fiscalite';
$action = 'consulter';

// simulation of session values
$clientId = null; // in portal expert, it depends... let's check
$entrepriseId = null; 

$contextId = $entrepriseId ?? $clientId;
echo "Context ID is: " . var_export($contextId, true) . "\n";

$hasPerm1 = $user->hasPermissionTo($module, $action, $contextId);
echo "HasPerm1 (contextId): " . ($hasPerm1 ? 'true' : 'false') . "\n";

$cabinetId = $user->affectations()->where('statut', 'actif')->first()?->entreprise_id;
echo "Cabinet ID is: " . var_export($cabinetId, true) . "\n";

$hasPerm2 = $cabinetId && $user->hasPermissionTo($module, $action, $cabinetId);
echo "HasPerm2 (cabinetId): " . ($hasPerm2 ? 'true' : 'false') . "\n";
