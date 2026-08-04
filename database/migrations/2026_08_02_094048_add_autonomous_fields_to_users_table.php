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
            $table->enum('workspace_type', ['entreprise', 'individuel'])->default('entreprise')->after('account_type');
            $table->timestamp('trial_ends_at')->nullable()->after('workspace_type');
            $table->enum('subscription_status', ['trial', 'active', 'expired', 'cancelled'])->nullable()->after('trial_ends_at');
            $table->unsignedBigInteger('plan_id')->nullable()->after('subscription_status');
            $table->string('personal_company_name')->nullable()->after('plan_id');
            $table->string('personal_industry')->nullable()->after('personal_company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'workspace_type',
                'trial_ends_at',
                'subscription_status',
                'plan_id',
                'personal_company_name',
                'personal_industry'
            ]);
        });
    }
};
