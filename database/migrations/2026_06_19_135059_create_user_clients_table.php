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
        Schema::create('user_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['super_admin', 'company_admin', 'company_manager', 'company_employee', 'client', 'comptable', 'caissier', 'juriste', 'rh', 'gestionnaire_projet', 'secretaire']);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_clients');
    }
};
