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
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id')->index();
            
            $table->enum('type', ['client', 'fournisseur', 'partenaire', 'administration', 'autre'])->default('autre');
            
            // Infos basiques
            $table->string('nom');
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            
            // Adresse
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('pays')->nullable();
            
            // Infos commerciales / légales
            $table->string('ifu')->nullable()->comment('Numéro fiscal / IFU');
            $table->string('site_web')->nullable();
            $table->string('devise_facturation')->default('XOF');
            $table->string('delai_paiement')->nullable(); // Ex: Net 30 jours
            $table->string('methode_paiement')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
