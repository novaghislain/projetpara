<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalogue_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // GS-AAAA-NNNNN
            $table->foreignId('client_id')->nullable()->constrained('users');
            $table->foreignId('service_id')->constrained('catalogue_services');
            $table->foreignId('categorie_id')->constrained('catalogue_categories');
            $table->string('statut')->default('Reçue');
            $table->foreignId('responsable_id')->nullable()->constrained('users');
            $table->dateTime('date_commande');
            $table->string('delai_estime')->nullable();
            $table->dateTime('date_livraison')->nullable();
            $table->decimal('montant_estime_fcfa', 12, 2)->nullable();
            $table->text('notes_internes')->nullable();
            $table->json('form_data')->nullable(); // pour stocker les champs dynamiques
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_orders');
    }
};
