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
        Schema::create('gel_rapprochements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('compte_id')->nullable();
            $table->decimal('solde_bancaire', 15, 2);
            $table->decimal('solde_comptable', 15, 2)->default(0);
            $table->date('date_rapprochement');
            $table->string('statut')->default('En cours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gel_rapprochements');
    }
};
