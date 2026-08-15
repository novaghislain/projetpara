<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$tables = [

    // ── Procès-Verbaux ───────────────────────────────────────────────────
    'dae_meeting_minutes' => function(Blueprint $t) {
        $t->uuid('id')->primary();
        $t->uuid('client_id')->nullable()->index();
        $t->string('titre');
        $t->text('objet')->nullable();
        $t->string('lieu')->nullable();
        $t->date('date_reunion');
        $t->time('heure_debut')->nullable();
        $t->time('heure_fin')->nullable();
        $t->json('participants')->nullable();
        $t->text('ordre_du_jour')->nullable();
        $t->text('discussion')->nullable();
        $t->text('decisions')->nullable();
        $t->date('prochaine_reunion')->nullable();
        $t->string('statut')->default('brouillon'); // brouillon, valide, approuve
        $t->uuid('redige_par')->nullable();
        $t->uuid('approuve_par')->nullable();
        $t->timestamp('approuve_at')->nullable();
        $t->uuid('created_by')->nullable();
        $t->timestamps();
        $t->softDeletes();
    },

    // ── Réunions ─────────────────────────────────────────────────────────
    'reunions' => function(Blueprint $t) {
        $t->uuid('id')->primary();
        $t->uuid('client_id')->nullable()->index();
        $t->string('titre');
        $t->text('description')->nullable();
        $t->string('type')->default('interne'); // interne, client, externe
        $t->string('lieu')->nullable();
        $t->datetime('date_debut');
        $t->datetime('date_fin')->nullable();
        $t->json('participants')->nullable();
        $t->string('statut')->default('planifie'); // planifie, en_cours, termine, annule
        $t->text('compte_rendu')->nullable();
        $t->uuid('organisateur_id')->nullable();
        $t->uuid('created_by')->nullable();
        $t->timestamps();
        $t->softDeletes();
    },

    // ── Messagerie ───────────────────────────────────────────────────────
    'gel_messages' => function(Blueprint $t) {
        $t->uuid('id')->primary();
        $t->uuid('cabinet_id')->nullable()->index();
        $t->uuid('client_id')->nullable()->index();
        $t->uuid('sender_id')->nullable();
        $t->string('sender_type')->nullable(); // user, client_contact
        $t->uuid('receiver_id')->nullable();
        $t->string('channel')->default('interne'); // interne, email, sms
        $t->text('message');
        $t->string('piece_jointe')->nullable();
        $t->boolean('est_lu')->default(false);
        $t->uuid('portal_contact_id')->nullable();
        $t->timestamps();
        $t->softDeletes();
    },

    // ── Relances ─────────────────────────────────────────────────────────
    'relance_rules' => function(Blueprint $t) {
        $t->uuid('id')->primary();
        $t->uuid('client_id')->nullable()->index();
        $t->string('name');
        $t->integer('trigger_days')->default(7);
        $t->string('channel')->default('email'); // email, sms
        $t->string('template_subject')->nullable();
        $t->text('template_body')->nullable();
        $t->boolean('is_active')->default(true);
        $t->timestamps();
        $t->softDeletes();
    },

    // ── Appels téléphoniques ──────────────────────────────────────────────
    'client_call_logs' => function(Blueprint $t) {
        $t->uuid('id')->primary();
        $t->uuid('client_id')->nullable()->index();
        $t->uuid('user_id')->nullable()->index();
        $t->string('direction')->default('entrant'); // entrant, sortant
        $t->string('contact_name')->nullable();
        $t->string('phone')->nullable();
        $t->text('notes')->nullable();
        $t->string('statut')->default('termine'); // planifie, en_cours, termine, absent, rappeler
        $t->timestamp('called_at')->nullable();
        $t->integer('duration_minutes')->nullable();
        $t->timestamps();
        $t->softDeletes();
    },

    // ── Contrats ─────────────────────────────────────────────────────────
    'dae_contrats' => function(Blueprint $t) {
        $t->uuid('id')->primary();
        $t->uuid('client_id')->nullable()->index();
        $t->string('reference')->nullable();
        $t->string('titre');
        $t->string('type_contrat')->nullable();
        $t->string('partie_adverse')->nullable();
        $t->date('date_signature')->nullable();
        $t->date('date_debut')->nullable();
        $t->date('date_fin')->nullable();
        $t->integer('duree_mois')->nullable();
        $t->decimal('montant', 15, 2)->nullable();
        $t->string('devise')->default('XOF');
        $t->text('objet')->nullable();
        $t->text('conditions')->nullable();
        $t->string('statut')->default('en_vigueur'); // brouillon, en_vigueur, expire, resilie, renouvele
        $t->string('fichier')->nullable();
        $t->date('date_preavis')->nullable();
        $t->date('date_renouvellement')->nullable();
        $t->boolean('renouvelable')->default(false);
        $t->date('renouvele_le')->nullable();
        $t->json('tags')->nullable();
        $t->uuid('created_by')->nullable();
        $t->uuid('updated_by')->nullable();
        $t->timestamps();
        $t->softDeletes();
    },
];

$created = [];
$skipped = [];

foreach ($tables as $table => $blueprint) {
    if (!Schema::hasTable($table)) {
        Schema::create($table, $blueprint);
        echo "CREATED: $table\n";
        $created[] = $table;
    } else {
        echo "SKIP (exists): $table\n";
        $skipped[] = $table;
    }
}

echo "\n=== Done: " . count($created) . " created, " . count($skipped) . " skipped ===\n";
