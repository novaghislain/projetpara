<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter 'active' et 'passive' à l'ENUM type pour compatibilité SYSCOHADA
        DB::statement("ALTER TABLE accounting_accounts MODIFY COLUMN type ENUM('asset','liability','equity','revenue','expense','active','passive','charge','produit') NOT NULL DEFAULT 'asset'");

        Schema::table('accounting_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_accounts', 'tenant_id')) {
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
        Schema::table('accounting_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_accounts', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
