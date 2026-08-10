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
        Schema::table('gel_factures', function (Blueprint $table) {
            $table->uuid('entreprise_id')->nullable()->after('id');
            $table->decimal('total_ht', 15, 2)->default(0)->after('montant');
            $table->decimal('total_tva', 15, 2)->default(0)->after('total_ht');
            $table->decimal('total_ttc', 15, 2)->default(0)->after('total_tva');
            $table->uuid('ecriture_comptable_id')->nullable()->after('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gel_factures', function (Blueprint $table) {
            $table->dropColumn(['entreprise_id', 'total_ht', 'total_tva', 'total_ttc', 'ecriture_comptable_id']);
        });
    }
};
