<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_scolaire_eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('matricule')->nullable();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('sexe')->nullable();
            $table->string('classe');
            $table->string('annee_scolaire');
            $table->string('niveau')->nullable(); // primaire, college, lycee
            $table->string('statut')->default('actif'); // actif, inactif, diplome, exclu
            $table->string('nom_tuteur')->nullable();
            $table->string('contact_tuteur')->nullable();
            $table->string('email_tuteur')->nullable();
            $table->text('adresse')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'matricule']);
            $table->index(['client_id', 'classe', 'annee_scolaire'], 'scol_eleves_cli_classe');
            $table->index(['client_id', 'statut'], 'scol_eleves_cli_stat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_scolaire_eleves');
    }
};
