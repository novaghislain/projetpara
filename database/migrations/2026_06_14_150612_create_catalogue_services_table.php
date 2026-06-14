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
        Schema::create('catalogue_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('catalogue_categories')->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->json('inclus_json')->nullable();
            $table->string('delai_jours')->nullable();
            $table->decimal('tarif_fcfa', 12, 2)->nullable();
            $table->string('tarif_type')->default('fixe'); // fixe, devis
            $table->json('documents_requis_json')->nullable();
            $table->json('champs_formulaire_json')->nullable();
            $table->boolean('actif')->default(true);
            $table->integer('ordre_affichage')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_services');
    }
};
