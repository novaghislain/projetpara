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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('sigle')->nullable()->after('nom_entreprise');
            $table->string('ville')->nullable()->after('adresse');
            $table->string('rc')->nullable()->after('nif');
            $table->string('secteur')->nullable()->after('rc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['sigle', 'ville', 'rc', 'secteur']);
        });
    }
};
