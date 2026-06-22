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
        // PostgreSQL : supprimer l'ancienne contrainte CHECK et en créer une avec les nouvelles valeurs
        DB::statement("ALTER TABLE clients DROP CONSTRAINT IF EXISTS clients_status_check");
        DB::statement("ALTER TABLE clients ADD CONSTRAINT clients_status_check CHECK (status IN ('actif','inactif','suspendu','prospect'))");

        DB::statement("ALTER TABLE missions DROP CONSTRAINT IF EXISTS missions_status_check");
        DB::statement("ALTER TABLE missions ADD CONSTRAINT missions_status_check CHECK (status IN ('a_faire','en_cours','termine','annule','terminee','annulee','en_attente'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE clients DROP CONSTRAINT IF EXISTS clients_status_check");
        DB::statement("ALTER TABLE clients ADD CONSTRAINT clients_status_check CHECK (status IN ('actif','inactif','suspendu'))");

        DB::statement("ALTER TABLE missions DROP CONSTRAINT IF EXISTS missions_status_check");
        DB::statement("ALTER TABLE missions ADD CONSTRAINT missions_status_check CHECK (status IN ('a_faire','en_cours','termine','annule'))");
    }
};
