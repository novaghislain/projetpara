<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_secretaire')) {
                $table->boolean('role_secretaire')->default(false)->after('role_id');
            }
            if (!Schema::hasColumn('users', 'clients_assignes')) {
                $table->json('clients_assignes')->default('[]')->after('role_secretaire');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role_secretaire', 'clients_assignes']);
        });
    }
};
