<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return; // SQLite (test) ne supporte pas ALTER MODIFY
        }
        // MySQL ENUM cannot be modified via Blueprint; use raw SQL
        DB::statement("ALTER TABLE `journals` MODIFY `type` VARCHAR(50) NOT NULL DEFAULT 'od'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // Restore the original ENUM (irreversible if data uses new values, but best effort)
        DB::statement("ALTER TABLE `journals` MODIFY `type` ENUM('ventes','achats','banque','caisse','paie','od','inventaire','financier') NOT NULL DEFAULT 'od'");
    }
};
