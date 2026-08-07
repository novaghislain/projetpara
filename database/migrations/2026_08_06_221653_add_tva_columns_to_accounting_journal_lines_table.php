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
        Schema::table('accounting_journal_lines', function (Blueprint $table) {
            $table->string('tva_code')->nullable()->after('credit');
            $table->decimal('tva_rate', 5, 2)->nullable()->after('tva_code');
            $table->decimal('tva_amount', 15, 2)->nullable()->after('tva_rate');
            $table->string('tva_type')->nullable()->after('tva_amount');
            $table->decimal('aib_rate', 5, 2)->nullable()->after('tva_type');
            $table->decimal('aib_amount', 15, 2)->nullable()->after('aib_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_journal_lines', function (Blueprint $table) {
            $table->dropColumn(['tva_code', 'tva_rate', 'tva_amount', 'tva_type', 'aib_rate', 'aib_amount']);
        });
    }
};
