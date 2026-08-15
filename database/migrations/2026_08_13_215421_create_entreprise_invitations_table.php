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
        Schema::create('entreprise_invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid('entreprise_id');
            $table->uuid('invited_by_user_id');
            $table->string('email');
            $table->string('role_invite'); // 'comptable', 'secretaire', etc.
            $table->string('token')->unique();
            $table->string('statut')->default('en_attente'); // 'en_attente', 'acceptee', 'expiree', 'rejetee'
            $table->timestamp('expire_at');
            $table->timestamp('acceptee_at')->nullable();
            $table->timestamps();

            // Clés étrangères
            // En fonction de la config UUID, ajustez si la db a des contraintes.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprise_invitations');
    }
};
