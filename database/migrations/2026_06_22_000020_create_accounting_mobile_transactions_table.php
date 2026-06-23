<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_mobile_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('reference_transaction')->nullable();
            $table->string('operateur')->default('mtn'); // mtn, orange, moov, celpaid, andere
            $table->string('type')->default('depot'); // depot, retrait, transfert, paiement, remboursement
            $table->string('numero_expediteur')->nullable();
            $table->string('numero_destinataire')->nullable();
            $table->string('nom_expediteur')->nullable();
            $table->string('nom_destinataire')->nullable();
            $table->decimal('montant', 12, 2)->default(0);
            $table->decimal('frais', 12, 2)->default(0);
            $table->decimal('montant_net', 12, 2)->default(0);
            $table->decimal('solde_avant', 12, 2)->nullable();
            $table->decimal('solde_apres', 12, 2)->nullable();
            $table->dateTime('date_transaction');
            $table->string('statut')->default('effectuee'); // effectuee, en_attente, echouee, remboursee
            $table->text('motif')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'reference_transaction'], 'mobile_txn_cli_ref');
            $table->index(['client_id', 'operateur'], 'mobile_cli_op');
            $table->index(['client_id', 'statut'], 'mobile_cli_stat');
            $table->index(['client_id', 'date_transaction'], 'mobile_cli_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_mobile_transactions');
    }
};
