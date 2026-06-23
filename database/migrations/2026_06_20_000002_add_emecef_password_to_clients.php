<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->text('emecef_password')->nullable()->after('emecef_is_active')
                  ->comment('Mot de passe e-MECeF configuré par l\'admin entreprise (chiffré)');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('emecef_password');
        });
    }
};
