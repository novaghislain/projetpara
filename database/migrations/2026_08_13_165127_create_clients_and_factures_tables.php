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
        // Table des clients (lié à une entreprise)
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
            $table->string('nom_entreprise'); // Nom de l'entreprise cliente
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->string('nif')->nullable(); // Numéro d'Identifiant Fiscal
            $table->string('statut')->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });

        // Table des factures
        Schema::create('factures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('numero')->unique(); // ex: FAC-2026-001
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();
            $table->decimal('montant_ht', 15, 2)->default(0);
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2)->default(0);
            $table->string('statut')->default('brouillon'); // brouillon, envoyee, payee, en_retard, annulee
            $table->timestamps();
            $table->softDeletes();
        });

        // Lignes de facture
        Schema::create('lignes_facture', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('facture_id')->constrained('factures')->cascadeOnDelete();
            $table->string('designation');
            $table->integer('quantite')->default(1);
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('taux_tva', 5, 2)->default(18); // 18% par défaut au Bénin
            $table->decimal('total_ht', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_facture');
        Schema::dropIfExists('factures');
        Schema::dropIfExists('clients');
    }
};
