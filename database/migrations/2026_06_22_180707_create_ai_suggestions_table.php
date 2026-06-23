<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('agent')->index();                      // ohada, fiscal, reconciliation, relance, ocr, cashflow
            $table->string('type');                                 // ecriture, relance, classification, alerte, reconciliation, prediction
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('data')->nullable();                      // données contextuelles
            $table->json('metadata')->nullable();                  // meta-informations (confiance, modèle, version)
            $table->string('status')->default('pending');          // pending, approved, rejected, applied
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('read_at')->nullable();              // marquer comme lu
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'agent', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_suggestions');
    }
};
