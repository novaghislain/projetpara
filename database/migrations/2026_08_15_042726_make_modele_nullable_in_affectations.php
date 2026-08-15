<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rend la colonne `modele` nullable dans `affectations`.
     * Elle était NOT NULL sans valeur par défaut, ce qui causait une erreur
     * lors de la création d'affectations sans spécifier de modèle (ex: invitations).
     */
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->string('modele')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->string('modele')->nullable(false)->change();
        });
    }
};
