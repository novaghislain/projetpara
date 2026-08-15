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
        Schema::create('dae_dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->string('type_demarche'); // ex: DGI_QUITUS, CNSS_IMMATRICULATION, DOUANE_DECLARATION
            $table->string('statut')->default('brouillon'); // brouillon, soumis, en_attente_institution, valide, rejete
            $table->string('reference_institution')->nullable(); // Numéro fourni par la DGI/CNSS
            $table->json('documents_joints')->nullable(); // Liste des IDs de documents (table documents)
            $table->json('meta_data')->nullable(); // Données JSON dynamiques selon la démarche
            $table->text('notes_institution')->nullable(); // Retours de l'administration
            $table->timestamp('soumis_le')->nullable();
            $table->timestamp('cloture_le')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dae_dossiers');
    }
};
