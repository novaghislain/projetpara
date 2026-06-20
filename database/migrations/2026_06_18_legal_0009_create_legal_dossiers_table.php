<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_dossiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('reference', 100);
            $table->string('titre', 255);
            $table->string('type', 100)->comment('formalité_constitution, modification_statuts, cession_parts, dissolution, autre');
            $table->enum('statut', ['ouvert', 'en_cours', 'en_attente', 'clôturé', 'archivé'])->default('ouvert');
            $table->enum('priorite', ['normale', 'urgente', 'critique'])->default('normale');
            $table->text('description')->nullable();
            $table->json('documents')->nullable();
            $table->json('dates')->nullable()->comment('{ouverture, echeance, cloture}');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'ldos_cli_statut_idx');
            $table->index(['client_id', 'priorite'], 'ldos_cli_prio_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_dossiers');
    }
};
