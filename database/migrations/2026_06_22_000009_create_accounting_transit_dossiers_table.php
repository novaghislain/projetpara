<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_transit_dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('reference_dossier')->unique();
            $table->string('type_transit'); // import, export, transitaire, douane
            $table->string('fournisseur_nom')->nullable();
            $table->string('client_nom')->nullable();
            $table->string('marchandise')->nullable();
            $table->decimal('valeur_marchandise', 14, 2)->default(0);
            $table->decimal('fret_ht', 12, 2)->default(0);
            $table->decimal('droits_douane', 12, 2)->default(0);
            $table->decimal('tva_douane', 12, 2)->default(0);
            $table->decimal('frais_accessoires', 12, 2)->default(0);
            $table->decimal('total_facture', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('en_cours'); // en_cours, finalise, facture, paye, annule
            $table->date('date_ouverture');
            $table->date('date_cloture')->nullable();
            $table->string('douane_bureau')->nullable();
            $table->string('numero_declaration')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'transit_cli_stat');
            $table->index(['client_id', 'type_transit'], 'transit_cli_type');
            $table->index(['client_id', 'date_ouverture'], 'transit_cli_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_transit_dossiers');
    }
};
