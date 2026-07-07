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
        Schema::create('journals', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10); // Ex: VTE, ACH, BNQ
            $table->string('intitule');
            $table->enum('type', ['ventes', 'achats', 'banque', 'caisse', 'paie', 'od', 'inventaire', 'financier']);
            $table->foreignId('compte_defaut_id')->nullable()->constrained('comptes')->nullOnDelete();
            $table->boolean('is_actif')->default(true);
            $table->timestamps();
            
            $table->unique(['client_id', 'code']);

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
