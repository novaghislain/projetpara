<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('reference_message')->nullable();
            $table->string('from_address');
            $table->json('to_addresses');
            $table->json('cc_addresses')->nullable();
            $table->string('objet');
            $table->longText('corps_html')->nullable();
            $table->longText('corps_texte')->nullable();
            $table->json('pieces_jointes')->nullable();
            $table->enum('statut', ['brouillon', 'envoye', 'recu', 'lu', 'archive'])->default('brouillon');
            $table->string('dossier')->nullable();
            $table->dateTime('date_reception')->nullable();
            $table->dateTime('date_envoi')->nullable();
            $table->dateTime('lu_at')->nullable();
            $table->foreignId('reponse_a_id')->nullable()->constrained('dae_emails')->nullOnDelete();
            $table->json('tags')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'dossier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_emails');
    }
};
