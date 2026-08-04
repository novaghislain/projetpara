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
        DB::statement("ALTER TABLE gel_client_invitations MODIFY COLUMN statut ENUM('en_attente', 'acceptee', 'expiree', 'rejetee') NOT NULL DEFAULT 'en_attente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE gel_client_invitations MODIFY COLUMN statut ENUM('en_attente', 'acceptee', 'expiree') NOT NULL DEFAULT 'en_attente'");
    }
};
