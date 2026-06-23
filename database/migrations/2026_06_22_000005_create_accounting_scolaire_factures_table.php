<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_scolaire_factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_facture')->unique();
            $table->string('annee_scolaire'); // 2025-2026
            $table->string('eleve_nom');
            $table->string('eleve_prenom')->nullable();
            $table->string('classe');
            $table->string('matricule')->nullable();
            $table->string('type_frais'); // scolarite, inscription, cantine, pension, transport, tenue, autre
            $table->string('periode')->nullable(); // 1er_trimestre, 2e_trimestre, 3e_trimestre, mensuel, annuel
            $table->decimal('montant_du', 12, 2)->default(0);
            $table->decimal('remise', 12, 2)->default(0);
            $table->decimal('montant_net', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('en_attente'); // en_attente, partielle, payee, annulee, impayee
            $table->date('date_echeance')->nullable();
            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'scol_fact_cli_stat');
            $table->index(['client_id', 'classe'], 'scol_fact_cli_classe');
            $table->index(['client_id', 'annee_scolaire'], 'scol_fact_cli_annee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_scolaire_factures');
    }
};
