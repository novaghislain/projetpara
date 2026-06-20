<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('reference', 100);
            $table->string('titre', 255);
            $table->enum('type', [
                'prestation_service', 'vente', 'bail_commercial', 'bail_habitation',
                'travail', 'partenariat', 'confidentialite_nda', 'cession_fonds',
                'licence', 'distribution', 'franchise', 'pret', 'cautionnement', 'autre'
            ]);
            $table->enum('statut', [
                'brouillon', 'en_négociation', 'signé', 'actif', 'expiré',
                'résilié', 'suspendu', 'contesté'
            ])->default('brouillon');
            $table->json('parties');
            $table->text('objet')->nullable();
            $table->date('date_signature')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('renouvellement_auto')->default(false);
            $table->integer('alerte_avant')->default(30)->comment('jours avant expiration');
            $table->decimal('montant', 18, 2)->nullable();
            $table->string('devise', 3)->default('XOF');
            $table->text('modalites_paiement')->nullable();
            $table->json('clauses_specifiques')->nullable();
            $table->text('penalites')->nullable();
            $table->string('tribunal_competent', 255)->nullable();
            $table->string('droit_applicable', 100)->default('Droit béninois (OHADA)');
            $table->string('document_path', 500)->nullable();
            $table->integer('version')->default(1);
            $table->json('historique_versions')->nullable();
            $table->foreignId('responsable')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['titre', 'objet']);
            $table->index(['client_id', 'statut', 'date_fin'], 'lc_cli_statut_fin_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_contracts');
    }
};
