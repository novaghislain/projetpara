<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tontines
        Schema::create('tontines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('name', 255);
            $table->enum('type', ['tournante', 'epargne', 'credit'])->default('tournante');
            $table->decimal('montant_cotisation', 12, 2);
            $table->enum('periodicite', ['hebdomadaire', 'quinzaine', 'mensuel'])->default('mensuel');
            $table->date('date_demarrage');
            $table->enum('statut', ['actif', 'clos', 'suspendu'])->default('actif');
            $table->timestamps();
        });

        // 2. Tontine members
        Schema::create('tontine_membres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tontine_id')->constrained('tontines')->cascadeOnDelete();
            $table->string('nom', 255);
            $table->string('telephone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->integer('ordre_tour')->nullable();
            $table->timestamps();
        });

        // 3. Tontine contributions
        Schema::create('tontine_cotisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tontine_id')->constrained('tontines');
            $table->foreignId('membre_id')->constrained('tontine_membres');
            $table->string('periode', 7); // YYYY-MM
            $table->decimal('montant', 12, 2);
            $table->enum('statut', ['attendue', 'payee', 'retard'])->default('attendue');
            $table->date('date_paiement')->nullable();
            $table->enum('mode_paiement', ['especes', 'momo', 'virement'])->nullable();
            $table->string('reference', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tontine_cotisations');
        Schema::dropIfExists('tontine_membres');
        Schema::dropIfExists('tontines');
    }
};
