<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_hotel_chambres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_chambre');
            $table->string('type')->default('standard'); // standard, suite, deluxe, presidentielle
            $table->string('categorie')->nullable(); // simple, double, twin, triple
            $table->decimal('prix_nuitee', 12, 2)->default(0);
            $table->integer('capacite')->default(1);
            $table->integer('etage')->nullable();
            $table->string('statut')->default('disponible'); // disponible, occupee, maintenance, reservee
            $table->text('equipements')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'numero_chambre']);
            $table->index(['client_id', 'statut'], 'hotel_chambres_cli_stat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_hotel_chambres');
    }
};
