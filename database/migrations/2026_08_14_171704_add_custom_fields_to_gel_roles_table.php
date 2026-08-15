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
        Schema::table('gel_roles', function (Blueprint $table) {
            $table->string('module')->nullable();
            $table->string('label_fr')->nullable();
            $table->text('description')->nullable();
            $table->integer('level')->default(0);
            $table->string('portail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gel_roles', function (Blueprint $table) {
            $table->dropColumn(['module', 'label_fr', 'description', 'level', 'portail']);
        });
    }
};
