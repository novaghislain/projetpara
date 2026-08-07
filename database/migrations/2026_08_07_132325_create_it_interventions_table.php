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
        Schema::create('it_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('it_mission_id')->constrained('it_missions')->onDelete('cascade');
            $table->foreignId('informaticien_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('status')->default('programmee'); // programmee, realisee
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('it_interventions');
    }
};
