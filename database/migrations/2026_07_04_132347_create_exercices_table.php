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
        Schema::create('exercices', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('libelle'); // Ex: Exercice 2026
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('is_clos')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('mois_clotures')->nullable(); // Liste des mois verrouillés ["01", "02"]
            $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercices');
    }
};
