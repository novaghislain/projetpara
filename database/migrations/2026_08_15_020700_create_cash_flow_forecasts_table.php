<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_flow_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->enum('type', ['in', 'out']); // in = encaissement, out = décaissement
            $table->decimal('amount', 15, 2);
            $table->date('expected_date');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, realized, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flow_forecasts');
    }
};
