<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gel_client_invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('gel_client_invitations', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('cabinet_id')->constrained('clients')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('gel_client_invitations', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
