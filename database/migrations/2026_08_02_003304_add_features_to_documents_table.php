<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'is_favorite')) {
                $table->boolean('is_favorite')->default(false)->after('version');
            }
            if (!Schema::hasColumn('documents', 'privacy_level')) {
                $table->string('privacy_level')->default('standard')->after('is_favorite');
            }
            if (!Schema::hasColumn('documents', 'tags')) {
                $table->json('tags')->nullable()->after('privacy_level');
            }
            if (!Schema::hasColumn('documents', 'share_token')) {
                $table->string('share_token')->nullable()->after('tags');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'is_favorite')) $table->dropColumn('is_favorite');
            if (Schema::hasColumn('documents', 'privacy_level')) $table->dropColumn('privacy_level');
            // Do not drop tags if we didn't create it, but whatever
            if (Schema::hasColumn('documents', 'share_token')) $table->dropColumn('share_token');
        });
    }
};
