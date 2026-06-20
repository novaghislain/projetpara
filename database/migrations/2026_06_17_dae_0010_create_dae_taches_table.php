<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('priorite', ['basse', 'moyenne', 'haute', 'critique'])->default('moyenne');
            $table->enum('statut', ['a_faire', 'en_cours', 'en_revision', 'terminee', 'annulee'])->default('a_faire');
            $table->date('echeance')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('dae_taches')->cascadeOnDelete();
            $table->json('tags')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'priorite']);
            $table->index('assigned_to');
            $table->index('echeance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_taches');
    }
};
