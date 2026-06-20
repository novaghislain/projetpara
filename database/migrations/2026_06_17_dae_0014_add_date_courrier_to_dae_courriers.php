<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dae_courriers', function (Blueprint $table) {
            $table->dateTime('date_courrier')->nullable()->after('urgence');
        });
    }

    public function down(): void
    {
        Schema::table('dae_courriers', function (Blueprint $table) {
            $table->dropColumn('date_courrier');
        });
    }
};
