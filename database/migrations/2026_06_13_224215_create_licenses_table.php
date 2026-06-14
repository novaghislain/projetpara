<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('license_key')->unique();
            $table->integer('duration_months');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['client_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
