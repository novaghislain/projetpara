<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('meeting_minutes')) {
            Schema::create('meeting_minutes', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('created_by')->nullable()->index();
                $table->string('titre')->nullable();
                $table->date('date_reunion')->nullable();
                $table->string('lieu')->nullable();
                $table->text('ordre_du_jour')->nullable();
                $table->text('compte_rendu')->nullable();
                $table->json('participants')->nullable();
                $table->string('statut')->default('brouillon');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('messagerie')) {
            Schema::create('messagerie', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('sender_id')->nullable()->index();
                $table->string('sender_type')->nullable();
                $table->text('contenu')->nullable();
                $table->boolean('est_lu')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('relances')) {
            Schema::create('relances', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('type_relance')->nullable();
                $table->string('destinataire')->nullable();
                $table->text('message')->nullable();
                $table->string('statut')->default('en_attente');
                $table->date('date_relance')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('call_logs')) {
            Schema::create('call_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('contact_nom')->nullable();
                $table->string('contact_numero')->nullable();
                $table->string('sens')->default('entrant'); // entrant, sortant
                $table->integer('duree_secondes')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('contrats')) {
            Schema::create('contrats', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('intitule')->nullable();
                $table->string('type')->nullable();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->string('statut')->default('actif');
                $table->string('fichier_path')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missing_core_tables');
    }
};
