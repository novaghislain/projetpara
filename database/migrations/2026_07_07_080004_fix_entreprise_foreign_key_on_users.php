<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop l'ancienne FK pointant vers gel_clients
            $table->dropForeign(['entreprise_id']);
            // Nouvelle FK pointant vers gel_entreprises
            $table->foreign('entreprise_id')
                  ->references('id')
                  ->on('gel_entreprises')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->foreign('entreprise_id')
                  ->references('id')
                  ->on('gel_clients')
                  ->onDelete('set null');
        });
    }
};
