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
        Schema::create('it_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('type'); // securite, maintenance, developpement
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('volume')->nullable(); // ex: "10 postes", "1 serveur"
            $table->string('status')->default('en_attente'); // en_attente, en_cours, terminee
            $table->timestamps();
        });

        Schema::create('it_mission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('it_mission_id')->constrained('it_missions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('it_mission_user');
        Schema::dropIfExists('it_missions');
    }
};
