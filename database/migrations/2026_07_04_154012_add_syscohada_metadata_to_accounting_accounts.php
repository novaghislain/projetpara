<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_accounts', 'account_nature')) {
                $table->enum('account_nature', ['debitor', 'creditor', 'bilateral'])
                    ->default('bilateral')
                    ->after('type');
            }
            if (!Schema::hasColumn('accounting_accounts', 'is_summary')) {
                $table->boolean('is_summary')->default(false)->after('account_nature');
            }
            if (!Schema::hasColumn('accounting_accounts', 'allow_journal_entry')) {
                $table->boolean('allow_journal_entry')->default(true)->after('is_summary');
            }
            if (!Schema::hasColumn('accounting_accounts', 'reconciliable')) {
                $table->boolean('reconciliable')->default(false)->after('allow_journal_entry');
            }
            if (!Schema::hasColumn('accounting_accounts', 'label_en')) {
                $table->string('label_en', 255)->nullable()->after('name');
            }
            if (!Schema::hasColumn('accounting_accounts', 'description')) {
                $table->text('description')->nullable()->after('label_en');
            }
            if (!Schema::hasColumn('accounting_accounts', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $columns = ['account_nature', 'is_summary', 'allow_journal_entry',
                        'reconciliable', 'label_en', 'description', 'sort_order'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('accounting_accounts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
