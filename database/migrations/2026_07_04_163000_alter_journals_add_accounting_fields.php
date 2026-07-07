<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            // Ajouter les colonnes manquantes pour le module écritures
            $table->foreignId('fiscal_year_id')->nullable()->constrained()->after('client_id');
            $table->string('prefix', 10)->nullable()->after('type');
            $table->integer('next_number')->default(1)->after('prefix');
            $table->text('description')->nullable()->after('is_actif');
            $table->boolean('is_default')->default(false)->after('description');
            $table->integer('sort_order')->default(0)->after('is_default');
            $table->softDeletes();

            // Renommer intitule → label, is_actif → is_active
            $table->renameColumn('intitule', 'label');
            $table->renameColumn('is_actif', 'is_active');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropForeign(['fiscal_year_id']);
            $table->dropColumn([
                'fiscal_year_id',
                'prefix',
                'next_number',
                'description',
                'is_default',
                'sort_order',
                'deleted_at',
            ]);
            $table->renameColumn('label', 'intitule');
            $table->renameColumn('is_active', 'is_actif');
        });
    }
};
