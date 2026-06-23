<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_quittances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_quittance')->unique();
            $table->string('bien')->nullable(); // description du bien loué
            $table->string('locataire_nom');
            $table->string('locataire_contact')->nullable();
            $table->string('periode'); // MMMM YYYY — ex: Juin 2026
            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('loyer_ht', 12, 2)->default(0);
            $table->decimal('charges', 12, 2)->default(0);
            $table->decimal('tva', 12, 2)->default(0);
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('en_attente'); // en_attente, payee, partielle, impayee, annulee
            $table->date('date_echeance')->nullable();
            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'quit_cli_stat');
            $table->index(['client_id', 'locataire_nom'], 'quit_cli_locataire');
            $table->index(['client_id', 'periode'], 'quit_cli_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_quittances');
    }
};
