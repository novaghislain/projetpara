<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_lignes_ecriture', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecriture_id')->constrained('gel_ecritures')->cascadeOnDelete();
            $table->foreignId('compte_id')->constrained('gel_comptes_comptables')->cascadeOnDelete();
            $table->enum('sens', ['debit', 'credit']);
            $table->decimal('montant', 15, 0)->default(0);
            $table->string('libelle_ligne', 255)->nullable();
            $table->foreignId('tiers_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->timestamps();
            $table->index(['ecriture_id', 'compte_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_lignes_ecriture');
    }
};
