<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('rh_employees')->cascadeOnDelete();
            $table->enum('type', ['conge', 'maladie', 'maternite', 'paternite', 'formation', 'autre'])->default('conge');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_jours');
            $table->text('motif')->nullable();
            $table->enum('statut', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approbateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes_approbateur')->nullable();
            $table->dateTime('date_approbation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'statut']);
            $table->index(['date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_leave_requests');
    }
};
