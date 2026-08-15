<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table gel_cabinets
 *
 * Un cabinet d'expertise comptable regroupe les clients, les utilisateurs,
 * le plan comptable, les journaux et les exercices comptables.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gel_cabinets')) {
            Schema::create('gel_cabinets', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('slug')->unique();
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->text('adresse')->nullable();
                $table->string('ville')->nullable();
                $table->string('ifu')->nullable();     // Identifiant Fiscal Unique
                $table->string('rc')->nullable();       // RCCM
                $table->string('logo')->nullable();     // Chemin du logo
                $table->boolean('actif')->default(true);
                $table->string('statut')->default('actif'); // actif, suspendu, fermé
                $table->json('config')->nullable();    // Configuration JSON du cabinet
                $table->json('limits')->nullable();    // Limites du plan
                // Abonnement
                $table->string('plan')->default('starter'); // starter, pro, enterprise
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('subscribed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Ajouter la colonne cabinet_id à la table utilisateurs si manquante
        if (Schema::hasTable('utilisateurs') && !Schema::hasColumn('utilisateurs', 'cabinet_id')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->unsignedBigInteger('cabinet_id')->nullable()->index()->after('id');
            });
        }

        // Ajouter cabinet_id à la table clients si manquante (pour la relation cabinet->clients)
        // La table clients est déjà gérée — on vérifie juste
        if (Schema::hasTable('clients') && !Schema::hasColumn('clients', 'cabinet_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->unsignedBigInteger('cabinet_id')->nullable()->index()->after('id');
            });
        }

        // Table gel_client_invitations
        if (!Schema::hasTable('gel_client_invitations')) {
            Schema::create('gel_client_invitations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('email');
                $table->string('token')->unique();
                $table->enum('statut', ['en_attente', 'acceptee', 'expiree'])->default('en_attente');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_client_invitations');
        Schema::dropIfExists('gel_cabinets');
    }
};
