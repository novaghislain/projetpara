<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_office_supply_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained('dae_office_supplies')->cascadeOnDelete();
            $table->integer('quantite_demandee');
            $table->integer('quantite_approuvee')->nullable();
            $table->text('motif')->nullable();
            $table->enum('statut', ['en_attente', 'approuvee', 'refusee', 'livree'])->default('en_attente');
            $table->foreignId('demande_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approuve_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['supply_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_office_supply_requests');
    }
};
