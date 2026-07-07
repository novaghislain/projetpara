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
        Schema::create('comptes', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete(); // Lié à l'entreprise cliente
            $table->string('numero', 20); // Ex: 411000
            $table->string('intitule');
            $table->integer('classe')->nullable(); // 1 à 8
            $table->enum('type', ['actif', 'passif', 'charge', 'produit', 'autre'])->default('autre');
            $table->string('sous_type')->nullable(); // Détail supplémentaire
            $table->foreignId('parent_id')->nullable()->constrained('comptes')->nullOnDelete(); // Hiérarchie
            $table->decimal('solde_debiteur', 15, 2)->default(0);
            $table->decimal('solde_crediteur', 15, 2)->default(0);
            $table->boolean('is_actif')->default(true); // Pour archivage
            $table->boolean('is_verrouille')->default(false); // Verrouiller un compte
            $table->timestamps();
            
            $table->unique(['client_id', 'numero']);

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
