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
        Schema::table('gel_messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->unsignedBigInteger('sender_id')->nullable()->change();
            $table->unsignedBigInteger('portal_contact_id')->nullable()->after('client_id');
            $table->foreign('portal_contact_id')->references('id')->on('portal_contacts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gel_messages', function (Blueprint $table) {
            $table->dropForeign(['portal_contact_id']);
            $table->dropColumn('portal_contact_id');
            // Impossible de recréer la contrainte si on a des nulls, donc on laisse le nullable
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
