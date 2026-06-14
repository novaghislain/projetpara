<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('pole_id')->constrained('poles')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['recurrent', 'ponctuel'])->default('ponctuel');
            $table->enum('status', ['a_faire', 'en_cours', 'termine', 'annule'])->default('a_faire');
            $table->enum('priority', ['basse', 'moyenne', 'haute', 'critique'])->default('moyenne');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('progress')->default(0); // 0-100
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
