<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_exercices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->string('libelle', 255);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('cloture')->default(false);
            $table->timestamp('cloture_at')->nullable();
            $table->foreignId('cloture_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['cabinet_id', 'client_id', 'date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_exercices');
    }
};
