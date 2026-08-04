<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le champ `account_context` à la table `users`.
     *
     * Un utilisateur peut avoir plusieurs contextes actifs simultanément.
     * Exemple : un comptable peut être à la fois :
     * - Invité par une entreprise (Modèle 1 : account_context contient 'model1')
     * - Indépendant avec ses propres clients (Modèle 3 : account_context contient 'model3_comptable')
     *
     * Ce champ est un JSON de la forme :
     * ["model1", "model3_comptable"] ou ["model3_secretaire"]
     *
     * L'isolation des données entre contextes est gérée au niveau des requêtes
     * (chaque contexte filtre sur ses propres relations).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('account_context')
                  ->nullable()
                  ->after('workspace_type')
                  ->comment('Contextes actifs de l\'utilisateur : model1, model2_gel_pool, model3_secretaire, model3_comptable');
            
            $table->string('active_account_context', 50)
                  ->nullable()
                  ->after('account_context')
                  ->comment('Contexte actuellement sélectionné si l\'utilisateur en a plusieurs');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_context', 'active_account_context']);
        });
    }
};
