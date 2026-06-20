<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_personnel_dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('poste')->nullable();
            $table->string('departement')->nullable();
            $table->date('date_embauche')->nullable();
            $table->date('date_depart')->nullable();
            $table->enum('statut', ['actif', 'conge', 'suspendu', 'sorti'])->default('actif');
            $table->string('type_contrat')->nullable();
            $table->decimal('salaire', 12, 2)->nullable();
            $table->string('numero_securite_sociale')->nullable();
            $table->json('documents')->nullable();
            $table->json('competences')->nullable();
            $table->json('informations_bancaires')->nullable();
            $table->json('urgences')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'departement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_personnel_dossiers');
    }
};
