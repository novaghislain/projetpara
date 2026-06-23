<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_grilles_tarifaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('designation');
            $table->string('categorie')->nullable(); // produit, service, abonnement
            $table->string('unite')->nullable();
            $table->decimal('prix_unitaire', 12, 2)->default(0);
            $table->decimal('tva', 5, 2)->default(0);
            $table->decimal('remise_max', 5, 2)->default(0);
            $table->date('date_validite_debut')->nullable();
            $table->date('date_validite_fin')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'code']);
            $table->index(['client_id', 'categorie'], 'grilles_cli_cat');
            $table->index(['client_id', 'is_active'], 'grilles_cli_actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_grilles_tarifaires');
    }
};
