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
        Schema::table('marketing_contents', function (Blueprint $table) {
            $table->string('theme')->nullable()->after('platform');
            $table->string('content_format')->nullable()->after('theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_contents', function (Blueprint $table) {
            $table->dropColumn(['theme', 'content_format']);
        });
    }
};
