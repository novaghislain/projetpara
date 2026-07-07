<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'tenant_id')) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->constrained('tenants')
                    ->nullOnDelete()
                    ->after('id');
            }
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_accounts', 'tenant_id')) {
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
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('bank_accounts', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
