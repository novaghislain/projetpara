<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('collaborator')->after('email');
            $table->foreignId('pole_id')->nullable()->constrained('poles')->nullOnDelete()->after('role');
            $table->string('phone')->nullable()->after('pole_id');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'pole_id', 'phone', 'is_active']);
        });
    }
};
