<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            if (!Schema::hasColumn('journals', 'tenant_id')) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->constrained('tenants')
                    ->nullOnDelete()
                    ->after('id');
            }
        });

        Schema::table('accounting_journals', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_journals', 'tenant_id')) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->constrained('tenants')
                    ->nullOnDelete()
                    ->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            if (Schema::hasColumn('journals', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });

        Schema::table('accounting_journals', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_journals', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
