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
        Schema::create('ligne_ecritures', function (Blueprint $table) {

            $table->id();
            $table->foreignId('ecriture_id')->constrained('ecritures')->cascadeOnDelete();
            $table->foreignId('compte_id')->constrained('comptes')->restrictOnDelete();
            $table->string('libelle')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->boolean('is_lettree')->default(false);
            $table->string('lettrage', 10)->nullable(); // Ex: AA, AB
            $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_ecritures');
    }
};
