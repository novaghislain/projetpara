<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_agenda_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['rdv', 'reunion', 'appel', 'echeance', 'autre'])->default('autre');
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('couleur')->nullable();
            $table->enum('statut', ['planifie', 'confirme', 'termine', 'annule'])->default('planifie');
            $table->json('rappel')->nullable();
            $table->json('participants')->nullable();
            $table->string('recurrence')->default('aucune');
            $table->date('recurrence_end')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'start_at']);
            $table->index('start_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_agenda_events');
    }
};
