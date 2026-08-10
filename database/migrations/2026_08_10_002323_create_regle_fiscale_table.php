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
        Schema::create('regle_fiscale', function (Blueprint $table) {
            $table->id();
            $table->uuid('entreprise_id')->nullable();
            $table->char('pays_code', 2)->default('BJ'); // BJ pour Bénin
            $table->string('type_impot'); // TVA | AIB | IS | ITS | TPS | PATENTE | VPS | CNSS
            $table->string('regime_fiscal')->nullable(); // reel_normal, reel_simplifie, etc.
            $table->date('date_debut_validite');
            $table->date('date_fin_validite')->nullable();
            $table->json('parametres'); // ex: { "taux": 0.18 }
            $table->uuid('compte_comptable_id')->nullable(); // Compte à impacter (ex: 4431)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regle_fiscale');
    }
};
