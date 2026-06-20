<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('rh_employees')->cascadeOnDelete();
            $table->string('titre');
            $table->string('organisme')->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->integer('duree_heures')->nullable();
            $table->decimal('cout', 15, 2)->nullable();
            $table->enum('type', ['interne', 'externe', 'en_ligne'])->default('interne');
            $table->string('certificat_url')->nullable();
            $table->enum('statut', ['planifie', 'en_cours', 'termine', 'annule'])->default('planifie');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_trainings');
    }
};
