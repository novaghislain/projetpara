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
        // Ajouter 'prospect' à l'enum status de clients
        DB::statement("ALTER TABLE `clients` CHANGE `status` `status` ENUM('actif','inactif','suspendu','prospect') NOT NULL DEFAULT 'actif'");

        // Ajouter 'terminee','annulee','en_attente' à l'enum status de missions
        DB::statement("ALTER TABLE `missions` CHANGE `status` `status` ENUM('a_faire','en_cours','termine','annule','terminee','annulee','en_attente') NOT NULL DEFAULT 'a_faire'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `clients` CHANGE `status` `status` ENUM('actif','inactif','suspendu') NOT NULL DEFAULT 'actif'");
        DB::statement("ALTER TABLE `missions` CHANGE `status` `status` ENUM('a_faire','en_cours','termine','annule') NOT NULL DEFAULT 'a_faire'");
    }
};
