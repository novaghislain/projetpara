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
        Schema::create('rapprochements', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('compte_id')->constrained('comptes')->cascadeOnDelete(); // Compte banque
            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('solde_depart', 15, 2)->default(0);
            $table->decimal('solde_fin', 15, 2)->default(0);
            $table->boolean('is_valide')->default(false);
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->json('lignes_rapprochees')->nullable(); // IDs des lignes ecritures pointées
            $table->string('rapport_pdf_path')->nullable();
            $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapprochements');
    }
};
