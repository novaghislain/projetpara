<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_revenue_recognition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->foreignId('compte_produit_id')->nullable()->constrained('gel_comptes_comptables')->nullOnDelete();
            $table->string('libelle', 255);
            $table->string('modele', 50); // service_interval, abonnement, etapes
            $table->decimal('montant_total', 15, 2)->default(0);
            $table->decimal('montant_differe', 15, 2)->default(0);
            $table->decimal('montant_reconnu', 15, 2)->default(0);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('frequence', 50)->nullable(); // mensuel, trimestriel, annuel
            $table->string('statut', 20)->default('en_cours'); // en_cours, termine, suspendu
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cabinet_id', 'client_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_revenue_recognition');
    }
};
