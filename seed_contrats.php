<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Mise à jour de la structure de dae_contrats...\n";

// Add missing columns using Schema builder
Schema::table('dae_contrats', function (Blueprint $table) {
    $cols = DB::select("SHOW COLUMNS FROM dae_contrats");
    $existing = array_map(fn($c) => $c->Field, $cols);
    
    if (!in_array('titre', $existing))          $table->string('titre')->nullable();
    if (!in_array('type_contrat', $existing))   $table->string('type_contrat')->nullable();
    if (!in_array('partie_adverse', $existing)) $table->string('partie_adverse')->nullable();
    if (!in_array('date_signature', $existing)) $table->date('date_signature')->nullable();
    if (!in_array('montant', $existing))        $table->decimal('montant', 15, 2)->nullable();
    if (!in_array('devise', $existing))         $table->string('devise', 10)->default('EUR');
    if (!in_array('objet', $existing))          $table->text('objet')->nullable();
    if (!in_array('conditions', $existing))     $table->text('conditions')->nullable();
    if (!in_array('duree_mois', $existing))     $table->integer('duree_mois')->nullable();
    if (!in_array('renouvelable', $existing))   $table->boolean('renouvelable')->default(false);
    if (!in_array('date_preavis', $existing))   $table->date('date_preavis')->nullable();
    if (!in_array('date_renouvellement', $existing)) $table->date('date_renouvellement')->nullable();
    if (!in_array('renouvele_le', $existing))   $table->date('renouvele_le')->nullable();
    if (!in_array('tags', $existing))           $table->json('tags')->nullable();
    if (!in_array('created_by', $existing))     $table->char('created_by', 36)->nullable();
    if (!in_array('updated_by', $existing))     $table->char('updated_by', 36)->nullable();
    if (!in_array('reference', $existing))      $table->string('reference')->nullable();
    if (!in_array('fichier', $existing))        $table->string('fichier')->nullable();
});

echo "Colonnes ajoutées !\n";

// Now insert demo data
use Illuminate\Support\Str;

$userId  = '019ffbc8-a7a6-7350-9fa0-548a848fc56c';
$client  = DB::table('clients')->first();

if (!$client) {
    echo "Aucun client trouvé.\n";
    exit(1);
}

$clientId = $client->id;
echo "Insertion des contrats pour client: {$client->nom_entreprise}\n";

$contrats = [
    [
        'client_id'      => $clientId,
        'titre'          => 'Contrat de prestation IT',
        'type_contrat'   => 'Prestation',
        'partie_adverse' => 'TechCorp Solutions',
        'date_signature' => '2025-06-15',
        'date_debut'     => '2025-07-01',
        'date_fin'       => '2026-07-01',
        'montant'        => 45000,
        'devise'         => 'EUR',
        'statut'         => 'actif',
        'renouvelable'   => 1,
        'created_by'     => $userId,
        'created_at'     => now(),
        'updated_at'     => now(),
    ],
    [
        'client_id'      => $clientId,
        'titre'          => 'Accord de Confidentialité (NDA)',
        'type_contrat'   => 'NDA',
        'partie_adverse' => 'InnovateX',
        'date_signature' => '2025-08-10',
        'date_debut'     => '2025-08-10',
        'date_fin'       => '2027-08-10',
        'montant'        => null,
        'devise'         => 'EUR',
        'statut'         => 'actif',
        'renouvelable'   => 0,
        'created_by'     => $userId,
        'created_at'     => now(),
        'updated_at'     => now(),
    ],
    [
        'client_id'      => $clientId,
        'titre'          => 'Contrat de maintenance des locaux',
        'type_contrat'   => 'Maintenance',
        'partie_adverse' => 'CleanPro',
        'date_signature' => '2024-01-01',
        'date_debut'     => '2024-01-01',
        'date_fin'       => '2024-12-31',
        'montant'        => 12000,
        'devise'         => 'EUR',
        'statut'         => 'expire',
        'renouvelable'   => 0,
        'created_by'     => $userId,
        'created_at'     => now()->subYear(),
        'updated_at'     => now()->subYear(),
    ],
    [
        'client_id'      => $clientId,
        'titre'          => 'Partenariat Stratégique Q3',
        'type_contrat'   => 'Partenariat',
        'partie_adverse' => 'Global Solutions Inc.',
        'date_signature' => null,
        'date_debut'     => null,
        'date_fin'       => null,
        'montant'        => 150000,
        'devise'         => 'EUR',
        'statut'         => 'brouillon',
        'renouvelable'   => 1,
        'created_by'     => $userId,
        'created_at'     => now()->subDays(2),
        'updated_at'     => now()->subDays(2),
    ],
];

DB::table('dae_contrats')->insert($contrats);

echo "Succès ! Total contrats: " . DB::table('dae_contrats')->count() . "\n";
