<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Chercher TOUS les dossiers liés à l'utilisateur 61, quelle que soit leur config
$allFolders = App\Models\ClientFolder::where('user_id', 61)
    ->orWhere('client_id', 27)
    ->get(['id', 'name', 'client_id', 'user_id', 'parent_id', 'created_at']);

echo "All folders for user 61 or client 27:\n";
echo json_encode($allFolders->toArray(), JSON_PRETTY_PRINT) . "\n";

// Aussi vérifier les derniers dossiers créés
$recent = App\Models\ClientFolder::latest()->take(5)->get(['id', 'name', 'client_id', 'user_id', 'created_at']);
echo "\nRecent folders:\n";
echo json_encode($recent->toArray(), JSON_PRETTY_PRINT) . "\n";
