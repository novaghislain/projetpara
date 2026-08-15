<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gel_permissions', function (Blueprint $table) {
            $table->string('module')->nullable();
            $table->string('label_fr')->nullable();
            $table->text('description')->nullable();
            $table->string('portail')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gel_permissions', function (Blueprint $table) {
            $table->dropColumn(['module', 'label_fr', 'description', 'portail']);
        });
    }
};
