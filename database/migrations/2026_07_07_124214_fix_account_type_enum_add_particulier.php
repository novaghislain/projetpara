<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('client','internal','super_admin','particulier','entreprise','cabinet') DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('client','internal','super_admin','entreprise','cabinet') DEFAULT NULL");
    }
};
