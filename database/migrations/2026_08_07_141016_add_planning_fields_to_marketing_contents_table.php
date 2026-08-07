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
            $table->dateTime('publish_date')->nullable()->after('client_feedback');
            $table->string('platform')->nullable()->after('publish_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_contents', function (Blueprint $table) {
            $table->dropColumn(['publish_date', 'platform']);
        });
    }
};
