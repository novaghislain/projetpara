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
        Schema::table('gel_audit_logs', function (Blueprint $table) {
            $table->string('actor_name')->nullable()->after('user_id');
            $table->string('actor_email')->nullable()->after('actor_name');
            $table->string('actor_role')->nullable()->after('actor_email');
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete()->after('actor_role');
            $table->string('session_id')->nullable()->after('user_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gel_audit_logs', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['actor_name', 'actor_email', 'actor_role', 'client_id', 'session_id']);
        });
    }
};
