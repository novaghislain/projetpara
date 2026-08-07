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
        Schema::create('consultant_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('consultant_missions')->onDelete('cascade');
            $table->string('email');
            $table->string('token')->unique();
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultant_invitations');
    }
};
