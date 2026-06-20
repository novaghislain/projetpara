<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dae_documents', function (Blueprint $table) {
            $table->foreignId('dossier_id')->nullable()->after('categorie')
                  ->constrained('dae_document_dossiers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dae_documents', function (Blueprint $table) {
            $table->dropForeign(['dossier_id']);
            $table->dropColumn('dossier_id');
        });
    }
};
