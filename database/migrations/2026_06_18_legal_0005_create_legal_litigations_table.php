<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_litigations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('reference', 100);
            $table->string('titre', 255);
            $table->enum('type', ['civil', 'commercial', 'social', 'fiscal', 'pénal', 'administratif', 'autre']);
            $table->enum('nature', ['demandeur', 'défendeur', 'tiers intervenant']);
            $table->string('partie_adverse', 255);
            $table->string('partie_adverse_avocat', 255)->nullable();
            $table->enum('statut', [
                'en_cours', 'instruction', 'plaidoirie', 'jugement_rendu',
                'appel', 'cassation', 'règlement_amiable',
                'clôturé_gagné', 'clôturé_perdu', 'clôturé_transaction'
            ])->default('en_cours');
            $table->string('tribunal', 255);
            $table->string('numero_dossier', 100)->nullable();
            $table->date('date_saisine')->nullable();
            $table->date('prochaine_audience')->nullable();
            $table->string('avocat_cabinet', 255)->nullable();
            $table->decimal('montant_litige', 18, 2)->nullable();
            $table->decimal('montant_risque', 18, 2)->nullable();
            $table->decimal('provisions_constituees', 18, 2)->default(0);
            $table->json('documents')->nullable();
            $table->json('historique')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'll_cli_statut_idx');
            $table->index(['client_id', 'type'], 'll_cli_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_litigations');
    }
};
