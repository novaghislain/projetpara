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
        Schema::create('courriers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Isolation tenant
            $table->uuid('client_id')->index();
            
            // Propriétés du courrier
            $table->string('numero_enregistrement')->unique(); // ex: GEL-2026-0001
            $table->enum('type', ['entrant', 'sortant', 'interne']);
            $table->string('objet');
            $table->string('expediteur_destinataire');
            $table->string('categorie')->nullable();
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            
            // Workflow à 6 étapes
            $table->enum('statut', ['creation', 'validation', 'visa', 'envoi', 'affectation', 'archive'])->default('creation');
            
            // Liens utilisateurs
            $table->uuid('assigne_a')->nullable()->comment('Utilisateur assigné (ex: comptable)');
            $table->uuid('cree_par')->nullable();
            
            // Dates
            $table->date('date_reception_envoi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courriers');
    }
};
