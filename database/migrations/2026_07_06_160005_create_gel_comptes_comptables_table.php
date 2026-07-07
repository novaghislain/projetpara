<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_comptes_comptables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('intitule', 255);
            $table->string('classe', 2); // 1-8
            $table->string('type', 20)->nullable(); // actif, passif, charge, produit
            $table->boolean('actif')->default(true);
            $table->boolean('syscohada')->default(false);
            $table->integer('niveau')->default(0);
            $table->string('code_parent', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['cabinet_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_comptes_comptables');
    }
};
