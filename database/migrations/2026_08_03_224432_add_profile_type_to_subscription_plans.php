<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le champ `profile_type` à la table `subscription_plans`.
     * Ce champ distingue à quel type de profil le plan s'applique,
     * afin d'éviter qu'un comptable souscrive au tarif d'un secrétaire et vice-versa.
     *
     * Valeurs possibles :
     * - 'entreprise'             : plan pour entreprise cliente (Modèle 1 ou 2)
     * - 'secretaire_independant': plan pour secrétaire autonome (Modèle 3A)
     * - 'comptable_independant' : plan pour comptable autonome (Modèle 3B)
     * - 'gel_pool'              : plan interne pour le personnel GEL SABINET (Modèle 2)
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('profile_type', 50)
                  ->default('entreprise')
                  ->after('is_active')
                  ->comment('À quel profil ce plan est destiné (entreprise, secretaire_independant, comptable_independant, gel_pool)');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('profile_type');
        });
    }
};
