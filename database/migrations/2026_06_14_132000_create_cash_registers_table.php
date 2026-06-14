<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users', 'id')->onDelete('cascade');
            $table->string('name');
            $table->string('code', 50);
            $table->enum('type', ['principal', 'auxiliaire'])->default('principal');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_open')->default(false);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('last_closed_at')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
