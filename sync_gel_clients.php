<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$userId = '019ffbc8-a7a6-7350-9fa0-548a848fc56c';

// IDs des clients dans user_clients (pointant vers table 'clients')
$clientLinks = DB::table('user_clients')
    ->where('user_id', $userId)
    ->pluck('client_id')
    ->toArray();

// Données des clients dans la table 'clients'
$clientsData = DB::table('clients')
    ->whereIn('id', $clientLinks)
    ->get();

echo "Synchronisation clients -> gel_clients...\n";

foreach ($clientsData as $c) {
    $exists = DB::table('gel_clients')->where('id', $c->id)->exists();
    if (!$exists) {
        DB::table('gel_clients')->insert([
            'id'             => $c->id,
            'nom_entreprise' => $c->nom_entreprise,
            'email'          => $c->email ?? null,
            'telephone'      => $c->telephone ?? null,
            'ville'          => $c->ville ?? null,
            'statut'         => $c->statut ?? 'actif',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        echo "  -> Ajouté dans gel_clients: {$c->nom_entreprise}\n";
    } else {
        echo "  -> Déjà présent dans gel_clients: {$c->nom_entreprise}\n";
    }
}

// Ajouter aussi les contrats dans dae_contrats avec les bons client_ids de gel_clients
// (déplacer les contrats vers les IDs de gel_clients si nécessaire)
$daeCount = DB::table('dae_contrats')->count();
echo "\nTotal gel_clients: " . DB::table('gel_clients')->count() . "\n";
echo "Total dae_contrats: {$daeCount}\n";
echo "Done!\n";
