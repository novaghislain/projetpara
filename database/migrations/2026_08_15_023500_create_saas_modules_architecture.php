<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_industries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->json('default_modules')->nullable();
            $table->timestamps();
        });

        Schema::table('gel_clients', function (Blueprint $table) {
            $table->foreignId('industry_id')->nullable()->constrained('saas_industries')->onDelete('set null');
            $table->json('active_modules')->nullable(); // Liste des modules activés pour ce tenant
        });
    }

    public function down(): void
    {
        Schema::table('gel_clients', function (Blueprint $table) {
            $table->dropForeign(['industry_id']);
            $table->dropColumn('industry_id');
            $table->dropColumn('active_modules');
        });

        Schema::dropIfExists('saas_industries');
    }
};
