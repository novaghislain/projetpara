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
            $table->integer('pool_max_capacity')->default(15)->after('wants_secretary');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_secretary_id')->nullable()->after('created_by');
            $table->unsignedBigInteger('assigned_accountant_id')->nullable()->after('assigned_secretary_id');
            $table->string('service_mode')->default('logiciel_seul')->after('status');

            $table->foreign('assigned_secretary_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_accountant_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['assigned_secretary_id']);
            $table->dropForeign(['assigned_accountant_id']);
            $table->dropColumn(['assigned_secretary_id', 'assigned_accountant_id', 'service_mode']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pool_max_capacity');
        });
    }
};
