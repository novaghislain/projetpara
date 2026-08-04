<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_cabinet_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained('gel_entreprises')->onDelete('cascade');
            $table->string('statut', 20)->default('actif');
            $table->timestamps();
            $table->unique(['cabinet_id', 'entreprise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_cabinet_clients');
    }
};
