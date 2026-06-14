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
        Schema::create('catalogue_order_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('catalogue_orders')->cascadeOnDelete();
            $table->foreignId('expediteur_id')->constrained('users');
            $table->string('type'); // client / equipe
            $table->text('contenu');
            $table->string('fichier_joint')->nullable();
            $table->boolean('lu')->default(false);
            $table->dateTime('date_lecture')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_order_messages');
    }
};
