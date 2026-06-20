<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->string('titre');
            $table->string('type_document')->nullable();
            $table->string('categorie')->nullable();
            $table->text('description')->nullable();
            $table->string('fichier');
            $table->bigInteger('taille_fichier')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('version')->default(1);
            $table->enum('statut', ['brouillon', 'final', 'archive', 'supprime'])->default('brouillon');
            $table->date('date_expiration')->nullable();
            $table->boolean('alerte_expiration')->default(false);
            $table->boolean('valide')->default(true);
            $table->boolean('signe')->default(false);
            $table->json('mots_cles')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'categorie']);
            $table->index('date_expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_documents');
    }
};
