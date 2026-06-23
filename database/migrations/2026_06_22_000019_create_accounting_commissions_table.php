<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_commission')->nullable();
            $table->string('type')->default('vente'); // vente, prestation, courtage, intermediaire
            $table->string('agent_nom');
            $table->string('agent_contact')->nullable();
            $table->decimal('montant_base', 12, 2)->default(0);
            $table->decimal('taux_commission', 5, 2)->default(0);
            $table->decimal('montant_commission', 12, 2)->default(0);
            $table->decimal('tva', 12, 2)->default(0);
            $table->decimal('montant_net', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->date('date_operation');
            $table->date('date_paiement')->nullable();
            $table->string('statut')->default('calculee'); // calculee, due, payee, annulee
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'commissions_cli_stat');
            $table->index(['client_id', 'date_operation'], 'commissions_cli_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_commissions');
    }
};
