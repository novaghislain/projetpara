<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titre', 255);
            $table->text('description')->nullable();
            $table->string('priorite', 20)->default('moyenne'); // basse, moyenne, haute, critique
            $table->string('statut', 20)->default('a_faire'); // a_faire, en_cours, termine, annule
            $table->date('date_echeance')->nullable();
            $table->timestamp('termine_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cabinet_id', 'statut', 'priorite']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_tasks');
    }
};
