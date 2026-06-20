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
            if (!Schema::hasColumn('users', 'active_client_id')) {
                $table->unsignedBigInteger('active_client_id')->nullable()->after('client_id');
                $table->foreign('active_client_id')->references('id')->on('clients')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('two_factor_confirmed_at');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'login_count')) {
                $table->integer('login_count')->default(0)->after('last_login_ip');
            }
            if (!Schema::hasColumn('users', 'is_suspended')) {
                $table->boolean('is_suspended')->default(false)->after('login_count');
            }
            if (!Schema::hasColumn('users', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('is_suspended');
            }
            if (!Schema::hasColumn('users', 'suspended_reason')) {
                $table->text('suspended_reason')->nullable()->after('suspended_at');
            }
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('suspended_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_client_id']);
            $table->dropColumn([
                'active_client_id', 'two_factor_secret', 'two_factor_recovery_codes',
                'two_factor_confirmed_at', 'last_login_at', 'last_login_ip',
                'login_count', 'is_suspended', 'suspended_at', 'suspended_reason',
                'must_change_password',
            ]);
        });
    }
};
