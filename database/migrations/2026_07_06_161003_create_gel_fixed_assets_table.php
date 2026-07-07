<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->foreignId('compte_id')->nullable()->constrained('gel_comptes_comptables')->nullOnDelete();
            $table->string('nom', 255);
            $table->text('description')->nullable();
            $table->date('date_acquisition');
            $table->decimal('cout_acquisition', 15, 2)->default(0);
            $table->decimal('valeur_residuelle', 15, 2)->default(0);
            $table->integer('duree_vie')->default(5); // en années
            $table->string('methode_amort', 20)->default('lineaire'); // lineaire, degressif
            $table->decimal('taux_amort', 5, 2)->default(0);
            $table->decimal('amort_cumule', 15, 2)->default(0);
            $table->decimal('vnc', 15, 2)->default(0); // valeur nette comptable
            $table->string('statut', 20)->default('actif'); // actif,cede,detruit
            $table->date('date_cession')->nullable();
            $table->decimal('prix_cession', 15, 2)->nullable();
            $table->decimal('plus_value', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cabinet_id', 'client_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_fixed_assets');
    }
};
