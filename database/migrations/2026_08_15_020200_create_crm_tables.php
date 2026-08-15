<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('contact_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->default('web');
            $table->string('status')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->string('title');
            $table->decimal('expected_revenue', 15, 2)->default(0);
            $table->integer('probability')->default(10);
            $table->date('expected_closing_date')->nullable();
            $table->string('stage')->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('leads');
    }
};
