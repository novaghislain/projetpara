<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->string('formule', 50); // start, standard, plus, advanced
            $table->string('statut', 50)->default('actif'); // actif, suspendu, resilie
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('stripe_id', 255)->nullable();
            $table->decimal('montant', 15, 2)->default(0);
            $table->string('devise', 5)->default('XOF');
            $table->integer('max_users')->default(1);
            $table->integer('max_clients')->default(10);
            $table->json('features')->nullable();
            $table->timestamps();
            $table->index(['cabinet_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_subscriptions');
    }
};
