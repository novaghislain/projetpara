<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_items', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('client_id', 'erp_items_client_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex('erp_items_client_idx');
            $table->dropColumn('client_id');
        });
    }
};
