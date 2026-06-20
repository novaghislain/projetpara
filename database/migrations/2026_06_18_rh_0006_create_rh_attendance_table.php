<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('rh_employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('heure_arrivee')->nullable();
            $table->time('heure_depart')->nullable();
            $table->json('pauses')->nullable(); // [{debut: "12:00", fin: "13:00"}]
            $table->decimal('heures_travaillees', 5, 2)->nullable();
            $table->enum('type_presence', ['present', 'abs', 'retard', 'conge', 'mission'])->default('present');
            $table->string('justificatif')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'type_presence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_attendance');
    }
};
