<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_journaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->string('code', 10);
            $table->string('libelle', 255);
            $table->string('type', 50)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->unique(['cabinet_id', 'client_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_journaux');
    }
};
