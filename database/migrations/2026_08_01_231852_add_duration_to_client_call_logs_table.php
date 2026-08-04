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
        Schema::table('client_call_logs', function (Blueprint $table) {
            $table->string('action_created')->nullable()->after('notes')->comment('Action générée : task, event, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_call_logs', function (Blueprint $table) {
            $table->dropColumn('action_created');
        });
    }
};
