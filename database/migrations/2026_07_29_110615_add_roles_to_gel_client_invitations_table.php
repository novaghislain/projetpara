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
        Schema::table('gel_client_invitations', function (Blueprint $table) {
            $table->string('role_invite')->default('comptable')->after('email')->comment('Le rôle de l\'utilisateur invité: comptable, secretaire, etc.');
            $table->unsignedBigInteger('invited_by_user_id')->nullable()->after('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gel_client_invitations', function (Blueprint $table) {
            $table->dropColumn(['role_invite', 'invited_by_user_id']);
        });
    }
};
