<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->string('registration_number')->unique();
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->string('status')->default('active');
            $table->integer('current_mileage')->default(0);
            $table->timestamps();
        });

        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('type');
            $table->text('description')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->integer('mileage_at_maintenance');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenances');
        Schema::dropIfExists('vehicles');
    }
};
