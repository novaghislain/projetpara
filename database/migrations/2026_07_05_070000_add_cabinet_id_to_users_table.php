<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cabinet_id')) {
                $table->unsignedBigInteger('cabinet_id')->nullable()->after('id');
                $table->index('cabinet_id', 'users_cabinet_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'cabinet_id')) {
                $table->dropIndex('users_cabinet_id_index');
                $table->dropColumn('cabinet_id');
            }
        });
    }
};
