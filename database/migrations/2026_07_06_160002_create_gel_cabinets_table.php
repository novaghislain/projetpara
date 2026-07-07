<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 200);
            $table->string('slug', 100)->unique();
            $table->string('email', 255)->nullable();
            $table->string('telephone', 50)->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville', 100)->nullable();
            $table->string('ifu', 50)->nullable();
            $table->string('rc', 50)->nullable();
            $table->string('logo', 255)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_cabinets');
    }
};
