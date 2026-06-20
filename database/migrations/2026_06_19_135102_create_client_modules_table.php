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
        Schema::create('client_modules', function (Blueprint $table) {
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('module', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->unsignedBigInteger('activated_by')->nullable();

            $table->primary(['client_id', 'module']);
            $table->foreign('activated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_modules');
    }
};
