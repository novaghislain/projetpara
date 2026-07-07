<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->string('nom', 255);
            $table->string('type', 50); // rappel_facture, rappel_paiement, alerte_depot, approbation
            $table->json('conditions')->nullable();
            $table->json('actions')->nullable();
            $table->boolean('actif')->default(true);
            $table->string('frequence', 50)->nullable(); // immediate, quotidien, hebdomadaire
            $table->timestamp('dernier_execution')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cabinet_id', 'type', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_workflows');
    }
};
