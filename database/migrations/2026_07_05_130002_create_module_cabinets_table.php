<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_cabinets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained()->cascadeOnDelete();
            $table->string('module', 100);
            $table->string('label', 255)->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('config')->nullable();
            $table->integer('max_users')->nullable();
            $table->integer('max_storage_mb')->nullable();
            $table->date('date_activation')->nullable();
            $table->date('date_expiration')->nullable();
            $table->timestamps();

            $table->unique(['cabinet_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_cabinets');
    }
};
