<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_learning_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('agent')->index();
            $table->string('type');                                 // suggestion_approved, suggestion_rejected, correction, feedback
            $table->text('input_data')->nullable();                 // ce que l'agent a reçu
            $table->text('output_data')->nullable();                // ce que l'agent a proposé
            $table->text('correction')->nullable();                 // correction humaine si applicable
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['client_id', 'agent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_learning_log');
    }
};
