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
            $table->string('pillar')->nullable()->after('content_format');
            $table->string('keywords')->nullable()->after('pillar');
            $table->string('rsd')->nullable()->after('keywords');
            $table->string('published_link')->nullable()->after('rsd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_contents', function (Blueprint $table) {
            $table->dropColumn(['pillar', 'keywords', 'rsd', 'published_link']);
        });
    }
};
