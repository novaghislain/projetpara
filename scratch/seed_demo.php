<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Entreprise;
use App\Models\Client;
use App\Models\Facture;
use App\Models\LigneFacture;

$entreprise = Entreprise::first();

if (!$entreprise) {
    echo "No entreprise found.\n";
    exit;
}

echo "Seeding for entreprise: {$entreprise->raison_sociale}\n";

$clientsData = [
    ['nom_entreprise' => 'Acme Corp', 'email' => 'contact@acme.com', 'telephone' => '+229 90 00 00 01'],
    ['nom_entreprise' => 'Globex Inc', 'email' => 'hello@globex.com', 'telephone' => '+229 90 00 00 02'],
    ['nom_entreprise' => 'Initech', 'email' => 'billing@initech.com', 'telephone' => '+229 90 00 00 03'],
];

$clients = [];
foreach ($clientsData as $c) {
    $clients[] = Client::create(array_merge($c, ['entreprise_id' => $entreprise->id]));
}
echo "Clients created.\n";

$facturesData = [
    ['client' => $clients[0], 'numero' => 'FAC-2026-001', 'montant_ht' => 500000, 'statut' => 'payee'],
    ['client' => $clients[1], 'numero' => 'FAC-2026-002', 'montant_ht' => 1250000, 'statut' => 'envoyee'],
    ['client' => $clients[2], 'numero' => 'FAC-2026-003', 'montant_ht' => 75000, 'statut' => 'brouillon'],
    ['client' => $clients[0], 'numero' => 'FAC-2026-004', 'montant_ht' => 250000, 'statut' => 'en_retard'],
];

foreach ($facturesData as $f) {
    $tva = $f['montant_ht'] * 0.18;
    $ttc = $f['montant_ht'] + $tva;
    
    $facture = Facture::create([
        'entreprise_id' => $entreprise->id,
        'client_id' => $f['client']->id,
        'numero' => $f['numero'],
        'date_emission' => now()->subDays(rand(1, 30)),
        'date_echeance' => now()->addDays(rand(-10, 15)),
        'montant_ht' => $f['montant_ht'],
        'montant_tva' => $tva,
        'montant_ttc' => $ttc,
        'statut' => $f['statut']
    ]);

    LigneFacture::create([
        'facture_id' => $facture->id,
        'designation' => 'Prestation de services',
        'quantite' => 1,
        'prix_unitaire' => $f['montant_ht'],
        'total_ht' => $f['montant_ht']
    ]);
}
echo "Factures created.\n";
