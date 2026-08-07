<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultant_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('gel_entreprises')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('consultant_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('title');
            $table->text('description');
            $table->string('specialty'); // Juriste, Fiscaliste, Expert RH, etc.
            $table->enum('status', ['en_attente', 'en_cours', 'livre', 'valide', 'cloture'])->default('en_attente');
            
            $table->date('start_date')->nullable();
            $table->date('end_date'); // Date de fin obligatoire
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultant_missions');
    }
};
