<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gel_entreprises', function (Blueprint $table) {
            if (!Schema::hasColumn('gel_entreprises', 'slug')) {
                $table->string('slug', 100)->nullable()->unique()->after('nom');
            }
            if (!Schema::hasColumn('gel_entreprises', 'pays')) {
                $table->string('pays', 100)->nullable()->after('ville');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gel_entreprises', function (Blueprint $table) {
            $table->dropColumn(['slug', 'pays']);
        });
    }
};
