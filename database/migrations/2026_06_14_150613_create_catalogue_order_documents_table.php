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
        Schema::create('catalogue_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('catalogue_orders')->cascadeOnDelete();
            $table->string('nom_fichier');
            $table->string('chemin_stockage');
            $table->string('type'); // client_fourni / gel_sabinet_livrable
            $table->integer('taille_ko')->nullable();
            $table->foreignId('id_user')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_order_documents');
    }
};
