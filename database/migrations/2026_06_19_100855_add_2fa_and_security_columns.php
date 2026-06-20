<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2FA columns on users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            }
        });

        // Security columns on clients
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'require_2fa')) {
                $table->boolean('require_2fa')->default(false)->after('notes');
            }
            if (!Schema::hasColumn('clients', 'session_timeout_minutes')) {
                $table->integer('session_timeout_minutes')->default(120)->after('require_2fa');
            }
            if (!Schema::hasColumn('clients', 'allowed_ips')) {
                $table->json('allowed_ips')->nullable()->after('session_timeout_minutes');
            }
            if (!Schema::hasColumn('clients', 'regime_fiscal')) {
                $table->string('regime_fiscal', 20)->nullable()->after('allowed_ips');
            }
            if (!Schema::hasColumn('clients', 'ifu')) {
                $table->string('ifu', 20)->nullable()->after('regime_fiscal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['require_2fa', 'session_timeout_minutes', 'allowed_ips', 'regime_fiscal', 'ifu']);
        });
    }
};
