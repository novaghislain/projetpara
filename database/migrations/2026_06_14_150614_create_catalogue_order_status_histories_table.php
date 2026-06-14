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
        Schema::create('catalogue_order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('catalogue_orders')->cascadeOnDelete();
            $table->string('statut_precedent')->nullable();
            $table->string('statut_nouveau');
            $table->foreignId('id_user')->constrained('users');
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_order_status_histories');
    }
};
