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
        Schema::table('user_permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('user_permissions', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade')->after('permission_id');
            }
            if (!Schema::hasColumn('user_permissions', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('granted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'expires_at']);
        });
    }
};
