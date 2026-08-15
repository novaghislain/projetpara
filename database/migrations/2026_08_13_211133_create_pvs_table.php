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
        Schema::create('pvs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id')->index();
            
            $table->uuid('event_id')->index(); // Lien vers la réunion
            $table->string('titre')->nullable();
            
            $table->longText('contenu')->nullable(); // Contenu du PV
            
            $table->enum('statut', ['brouillon', 'valide'])->default('brouillon');
            $table->uuid('cree_par')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pvs');
    }
};
