<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S3.3 — Stocke le texte extrait par OCR/AI (scan, recherche plein texte).
     * Aucune donnée supprimée ici : simple colonne additionnelle nullable.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->longText('text_content')->nullable()->after('description');
        });

        // Index FULLTEXT (InnoDB) pour la recherche par contenu.
        // Compatible MySQL ; ignoré silencieusement sous d'autres moteurs non supportés.
        try {
            Schema::table('documents', function (Blueprint $table) {
                $table->fullText(['text_content', 'name'], 'documents_search_ft');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FULLTEXT index non supporté : ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_search_ft');
            $table->dropColumn('text_content');
        });
    }
};