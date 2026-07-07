<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->text('objet')->nullable();
            $table->string('lieu')->nullable();
            $table->date('date_reunion');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->json('participants')->nullable();
            $table->json('ordre_du_jour')->nullable();
            $table->json('discussion')->nullable();
            $table->json('decisions')->nullable();
            $table->date('prochaine_reunion')->nullable();
            $table->enum('statut', ['projet', 'final', 'approuve'])->default('projet');
            $table->foreignId('redige_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approuve_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'date_reunion']);
            $table->index(['client_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_meeting_minutes');
    }
};
