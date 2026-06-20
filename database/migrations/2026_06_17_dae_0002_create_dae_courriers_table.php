<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_courriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->string('expediteur')->nullable();
            $table->string('destinataire')->nullable();
            $table->enum('type', ['entrant', 'sortant', 'interne'])->default('entrant');
            $table->enum('mode', ['postal', 'email', 'remise_main'])->default('postal');
            $table->string('objet');
            $table->text('contenu')->nullable();
            $table->enum('urgence', ['normal', 'urgent', 'tre_urgent'])->default('normal');
            $table->enum('statut', ['brouillon', 'envoye', 'recu', 'traite', 'archive'])->default('brouillon');
            $table->dateTime('date_reception')->nullable();
            $table->dateTime('date_envoi')->nullable();
            $table->dateTime('date_traitement')->nullable();
            $table->text('reponse')->nullable();
            $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            $table->string('fichier_joint')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'type']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_courriers');
    }
};
