<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Les entreprises auto-inscrites n'ont pas de cabinet comptable rattaché
        // à l'onboarding : la colonne doit être nullable.
        Schema::table('gel_clients', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('gel_clients', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->change();
        });
    }
};
