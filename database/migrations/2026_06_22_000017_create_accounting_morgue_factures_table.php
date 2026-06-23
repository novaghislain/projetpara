<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_morgue_factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_facture')->unique();
            $table->foreignId('depot_id')->nullable()->constrained('accounting_morgue_depots')->nullOnDelete();
            $table->string('client_nom');
            $table->string('defunt_nom');
            $table->string('type_prestation')->default('conservation'); // conservation, embaumement, soins, transport, cerueil, service
            $table->integer('nb_jours')->default(0);
            $table->decimal('montant_ht', 12, 2)->default(0);
            $table->decimal('tva', 12, 2)->default(0);
            $table->decimal('montant_ttc', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('en_attente'); // en_attente, payee, partielle, annulee
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'morgue_fact_cli_stat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_morgue_factures');
    }
};
