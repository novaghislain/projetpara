<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_rapports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->string('type_rapport')->nullable();
            $table->text('description')->nullable();
            $table->date('periode_debut')->nullable();
            $table->date('periode_fin')->nullable();
            $table->json('contenu')->nullable();
            $table->string('fichier')->nullable();
            $table->json('metriques')->nullable();
            $table->enum('statut', ['brouillon', 'genere', 'finalise', 'envoye'])->default('brouillon');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'type_rapport']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_rapports');
    }
};
