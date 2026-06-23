<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_emballages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // consigné, perdu, vendu, retourné, cassé
            $table->string('designation');
            $table->string('tiers_nom')->nullable(); // fournisseur ou client
            $table->string('tiers_type')->nullable(); // fournisseur, client
            $table->string('produit_concerne')->nullable();
            $table->integer('quantite')->default(1);
            $table->decimal('prix_unitaire', 12, 2)->default(0);
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('montant_consigne', 12, 2)->default(0);
            $table->decimal('solde_consigne', 12, 2)->default(0);
            $table->date('date_operation');
            $table->string('statut')->default('actif'); // actif, retourne, solde, perdu
            $table->string('reference_document')->nullable(); // facture, bon de livraison
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'type'], 'embal_cli_type');
            $table->index(['client_id', 'tiers_nom'], 'embal_cli_tiers');
            $table->index(['client_id', 'statut'], 'embal_cli_stat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_emballages');
    }
};
