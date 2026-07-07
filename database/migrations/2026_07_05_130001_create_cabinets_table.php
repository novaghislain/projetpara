<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 255);
            $table->string('slug', 255)->unique();
            $table->string('email', 255)->nullable();
            $table->string('telephone', 50)->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville', 100)->nullable();
            $table->string('pays', 100)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('site_web', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('ifu', 100)->nullable();
            $table->string('rccm', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->json('limits')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabinets');
    }
};
