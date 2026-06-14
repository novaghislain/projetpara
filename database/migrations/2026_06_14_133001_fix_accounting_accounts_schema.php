<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les colonnes SYSCOHADA à la table existante
        Schema::table('accounting_accounts', function (Blueprint $table) {
            // Classe SYSCOHADA (1-9)
            $table->char('syscohada_class', 1)->nullable()->after('code');
            // Hiérarchie parent-enfant pour arborescence
            $table->foreignId('parent_id')->nullable()->constrained('accounting_accounts')->nullOnDelete()->after('type');
            // Compte standard SYSCOHADA (non modifiable sauf ADMIN)
            $table->boolean('is_syscohada')->default(false)->after('parent_id');
            // TVA par défaut sur ce compte
            $table->decimal('tva_rate', 5, 2)->nullable()->after('is_active');
            $table->boolean('has_tva')->default(false)->after('tva_rate');

            $table->index('syscohada_class');
        });

        // Mettre à jour les comptes existants avec leur classe SYSCOHADA
        // La migration est sécurisée : les anciennes données avec type='asset'/'liability' etc. restent lisibles
        DB::statement("UPDATE accounting_accounts SET syscohada_class = '1' WHERE type = 'equity' AND syscohada_class IS NULL");
        DB::statement("UPDATE accounting_accounts SET syscohada_class = '2' WHERE type = 'asset' AND substring(code from 1 for 1) = '2' AND syscohada_class IS NULL");
        DB::statement("UPDATE accounting_accounts SET syscohada_class = '3' WHERE type = 'asset' AND substring(code from 1 for 1) = '3' AND syscohada_class IS NULL");
        DB::statement("UPDATE accounting_accounts SET syscohada_class = '4' WHERE type IN ('asset','liability') AND substring(code from 1 for 1) = '4' AND syscohada_class IS NULL");
        DB::statement("UPDATE accounting_accounts SET syscohada_class = '5' WHERE type = 'asset' AND substring(code from 1 for 1) = '5' AND syscohada_class IS NULL");
        DB::statement("UPDATE accounting_accounts SET syscohada_class = '6' WHERE type = 'expense' AND syscohada_class IS NULL");
        DB::statement("UPDATE accounting_accounts SET syscohada_class = '7' WHERE type = 'revenue' AND syscohada_class IS NULL");
    }

    public function down(): void
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropColumn(['syscohada_class', 'parent_id', 'is_syscohada', 'tva_rate', 'has_tva']);
        });
    }
};
