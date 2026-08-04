<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La colonne account_type existe déjà comme ENUM('client','internal','super_admin')
        // On l'étend pour accepter 'entreprise' et 'cabinet'
        Schema::table('users', function (Blueprint $table) {
            // MySQL ne permet pas d'ajouter des valeurs ENUM directement via Blueprint
            // On utilise une requête brute
        });

        \DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('client','internal','super_admin','entreprise','cabinet') DEFAULT NULL");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('client','internal','super_admin') DEFAULT 'client'");
    }
};
