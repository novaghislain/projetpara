<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_office_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('categorie')->nullable();
            $table->integer('quantite_stock')->default(0);
            $table->integer('seuil_alerte')->default(5);
            $table->string('unite')->default('piece');
            $table->decimal('prix_unitaire', 15, 2)->nullable();
            $table->string('fournisseur')->nullable();
            $table->string('emplacement')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'categorie']);
            $table->index(['client_id', 'quantite_stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_office_supplies');
    }
};
