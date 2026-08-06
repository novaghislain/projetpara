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
        Schema::create('business_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->string('destination');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('purpose');
            $table->string('status')->default('en_attente'); // en_attente, approuvé, rejeté, terminé
            $table->decimal('budget', 15, 2)->nullable();
            $table->text('transport_details')->nullable();
            $table->text('accommodation_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trips');
    }
};
