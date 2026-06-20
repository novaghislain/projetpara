<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_conformite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('type_conformite');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->text('exigence_reglementaire')->nullable();
            $table->string('autorite_competente')->nullable();
            $table->date('date_soumission')->nullable();
            $table->date('date_validation')->nullable();
            $table->date('date_expiration')->nullable();
            $table->enum('statut', ['a_faire', 'en_cours', 'valide', 'non_conforme', 'expire'])->default('a_faire');
            $table->json('pieces_jointes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'date_expiration']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_conformite');
    }
};
