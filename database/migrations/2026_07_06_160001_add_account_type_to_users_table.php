<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'account_type')) {
                $table->string('account_type', 20)->default('entreprise')->after('id');
            }
            if (!Schema::hasColumn('users', 'cabinet_id')) {
                $table->foreignId('cabinet_id')->nullable()->constrained('gel_cabinets')->nullOnDelete()->after('account_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'cabinet_id')) {
                $table->dropForeign(['cabinet_id']);
                $table->dropColumn('cabinet_id');
            }
            if (Schema::hasColumn('users', 'account_type')) {
                $table->dropColumn('account_type');
            }
        });
    }
};
