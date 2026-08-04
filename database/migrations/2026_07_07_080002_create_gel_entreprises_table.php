<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_entreprises', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 255);
            $table->string('ifu', 50)->nullable();
            $table->string('rc', 50)->nullable();
            $table->string('telephone', 30)->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville', 100)->nullable();
            $table->string('secteur', 255)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_entreprises');
    }
};
