<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // La secrétaire
            $table->string('direction')->default('entrant'); // entrant | sortant
            $table->string('contact_name')->nullable();     // Nom de l'interlocuteur
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();              // Compte-rendu de l'appel
            $table->string('statut')->default('terminé');   // terminé | sans-réponse | à rappeler
            $table->timestamp('called_at');
            $table->integer('duration_minutes')->nullable(); // Durée en minutes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_call_logs');
    }
};
