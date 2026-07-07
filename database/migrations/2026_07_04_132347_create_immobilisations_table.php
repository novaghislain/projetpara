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
        Schema::create('immobilisations', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('designation');
            $table->string('categorie'); // Terrain, Matériel, etc.
            $table->date('date_acquisition');
            $table->date('date_mise_service')->nullable();
            $table->decimal('valeur_acquisition', 15, 2);
            $table->integer('duree_amortissement'); // En mois ou années
            $table->enum('methode_amortissement', ['lineaire', 'degressif'])->default('lineaire');
            $table->decimal('amortissement_cumule', 15, 2)->default(0);
            $table->decimal('valeur_nette_comptable', 15, 2);
            $table->foreignId('compte_immobilisation_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignId('compte_amortissement_id')->nullable()->constrained('comptes')->restrictOnDelete();
            $table->foreignId('compte_dotation_id')->nullable()->constrained('comptes')->restrictOnDelete();
            $table->boolean('is_sortie')->default(false);
            $table->date('date_sortie')->nullable();
            $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('immobilisations');
    }
};
