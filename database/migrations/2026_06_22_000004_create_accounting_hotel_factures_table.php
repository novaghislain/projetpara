<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_hotel_factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_facture')->unique();
            $table->string('type'); // chambre, restauration, service, autre
            $table->string('client_nom')->nullable();
            $table->string('client_contact')->nullable();
            $table->string('chambre')->nullable();
            $table->date('date_arrivee')->nullable();
            $table->date('date_depart')->nullable();
            $table->integer('nb_nuitees')->default(1);
            $table->decimal('prix_nuitee', 12, 2)->default(0);
            $table->decimal('montant_ht', 12, 2)->default(0);
            $table->decimal('tva', 12, 2)->default(0);
            $table->decimal('taxe_sejour', 12, 2)->default(0);
            $table->decimal('remise', 12, 2)->default(0);
            $table->decimal('montant_ttc', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('en_attente'); // en_attente, payee, partielle, annulee
            $table->string('mode_paiement')->nullable();
            $table->json('services_supplementaires')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'hotel_fact_cli_stat');
            $table->index(['client_id', 'date_arrivee', 'date_depart'], 'hotel_fact_cli_dates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_hotel_factures');
    }
};
