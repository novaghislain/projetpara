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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id')->index();
            
            $table->string('titre');
            $table->text('description')->nullable();
            
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('lieu')->nullable();
            
            // Lien optionnel vers un contact
            $table->uuid('contact_id')->nullable()->index();
            
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
        Schema::dropIfExists('events');
    }
};
