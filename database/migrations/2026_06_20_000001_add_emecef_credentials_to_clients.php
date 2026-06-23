<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('emecef_nim')->nullable()->after('regime_fiscal')
                  ->comment('NIM e-MECeF fourni par la DGI pour ce client');
            $table->boolean('emecef_is_active')->default(false)->after('emecef_nim')
                  ->comment('Active/désactive l\'émission e-MECeF pour ce client');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['emecef_nim', 'emecef_is_active']);
        });
    }
};
