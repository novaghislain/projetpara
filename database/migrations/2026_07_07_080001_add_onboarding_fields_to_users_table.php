<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'account_type')) {
                $table->string('account_type', 20)->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'onboarding_token')) {
                $table->string('onboarding_token', 100)->nullable()->unique()->after('password');
            }
            if (!Schema::hasColumn('users', 'onboarding_completed')) {
                $table->boolean('onboarding_completed')->default(false)->after('onboarding_token');
            }
            if (!Schema::hasColumn('users', 'prenom')) {
                $table->string('prenom', 255)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->constrained('gel_clients')->onDelete('set null')->after('cabinet_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_token', 'onboarding_completed', 'prenom', 'entreprise_id', 'account_type']);
        });
    }
};
