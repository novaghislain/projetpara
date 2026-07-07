<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_saved_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->string('nom', 255);
            $table->string('type', 50); // bilan, resultat, grand_livre, balance, personnalise
            $table->json('filtres')->nullable();
            $table->json('colonnes')->nullable();
            $table->json('configuration')->nullable(); // tri, regroupe, format
            $table->boolean('partage')->default(false);
            $table->string('couleur', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cabinet_id', 'client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_saved_reports');
    }
};
