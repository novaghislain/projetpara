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
        DB::statement("ALTER TABLE users MODIFY account_type ENUM('client', 'internal', 'super_admin', 'particulier', 'entreprise', 'cabinet', 'informaticien', 'communication', 'consultant')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY account_type ENUM('client', 'internal', 'super_admin', 'particulier', 'entreprise', 'cabinet', 'informaticien', 'communication')");
    }
};
