<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$userId   = '019ffbc8-a7a6-7350-9fa0-548a848fc56c';
$clients  = DB::table('clients')->get(['id']);

// 1) Lier l'utilisateur à tous les clients via user_clients
echo "Liaison utilisateur <-> clients...\n";
foreach ($clients as $c) {
    $exists = DB::table('user_clients')
        ->where('user_id', $userId)
        ->where('client_id', $c->id)
        ->exists();
    if (!$exists) {
        DB::table('user_clients')->insert([
            'user_id'    => $userId,
            'client_id'  => $c->id,
            'role'       => 'secretaire',
            'is_active'  => 1,
            'joined_at'  => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "  -> client {$c->id} lié\n";
    } else {
        echo "  -> client {$c->id} déjà lié\n";
    }
}

// 2) Vérifier que les clients ont un cabinet_id (peut être null)
// Si le user n'a pas de cabinet_id, on doit s'assurer que les clients
// sont bien trouvés via user_clients
$total = DB::table('user_clients')->where('user_id', $userId)->count();
echo "\nTotal liaisons: {$total}\n";
echo "Done!\n";
