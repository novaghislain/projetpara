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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('active_independant_client_id')->nullable()->after('active_client_id');

            $table->foreign('active_independant_client_id', 'fk_user_active_indep_client')
                  ->references('id')
                  ->on('independant_comptable_clients')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('fk_user_active_indep_client');
            $table->dropColumn('active_independant_client_id');
        });
    }
};
