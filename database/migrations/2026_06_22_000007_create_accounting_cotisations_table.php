<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_cotisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('tontine_nom');
            $table->string('membre_nom');
            $table->string('membre_contact')->nullable();
            $table->string('periode'); // MMMM YYYY
            $table->date('date_echeance');
            $table->decimal('montant', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('en_attente'); // en_attente, payee, retard, annulee
            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'cotis_cli_stat');
            $table->index(['client_id', 'tontine_nom'], 'cotis_cli_tontine');
            $table->index(['client_id', 'membre_nom', 'periode'], 'cotis_cli_membre_per');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_cotisations');
    }
};
