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
        Schema::create('budgets', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('exercice_id')->nullable();
            $table->string('libelle'); // Budget 2026 Révisé
            $table->string('departement')->nullable(); // Centre de coût
            $table->json('lignes_budget'); // [compte_id => [jan => 1000, feb => 1000...]]
            $table->boolean('is_actif')->default(true);
            $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
