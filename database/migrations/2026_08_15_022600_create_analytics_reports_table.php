<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->string('type');
            $table->json('parameters')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
