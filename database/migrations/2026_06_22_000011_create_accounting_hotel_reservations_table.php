<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_hotel_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_reservation')->unique();
            $table->foreignId('chambre_id')->nullable()->constrained('accounting_hotel_chambres')->nullOnDelete();
            $table->string('client_nom');
            $table->string('client_contact')->nullable();
            $table->string('client_email')->nullable();
            $table->date('date_arrivee');
            $table->date('date_depart');
            $table->integer('nb_nuitees')->default(1);
            $table->integer('nb_adultes')->default(1);
            $table->integer('nb_enfants')->default(0);
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('acompte', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('confirmee'); // confirmee, en_cours, terminee, annulee, no_show
            $table->string('source')->nullable(); // direct, booking, expedia, etc.
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'hotel_res_cli_stat');
            $table->index(['client_id', 'date_arrivee', 'date_depart'], 'hotel_res_cli_dates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_hotel_reservations');
    }
};
