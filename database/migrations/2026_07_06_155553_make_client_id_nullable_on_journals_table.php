<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['client_id']);

            // Make client_id nullable (tenant_id is the primary multi-tenant key)
            $table->foreignId('client_id')
                ->nullable()
                ->change()
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropForeign(['client_id']);

            $table->foreignId('client_id')
                ->nullable(false)
                ->change()
                ->constrained()
                ->cascadeOnDelete();
        });
    }
};
