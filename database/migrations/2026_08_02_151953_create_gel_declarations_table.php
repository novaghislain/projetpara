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
        Schema::create('gel_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->cascadeOnDelete();
            $table->string('type'); // TVA, IS, AIB, VPS, etc.
            $table->string('periode'); // Ex: 2026-07
            $table->date('date_echeance');
            $table->date('date_soumission')->nullable();
            $table->string('statut')->default('brouillon'); // brouillon, a_soumettre, soumise, payee, en_retard
            $table->decimal('montant_du', 15, 2)->default(0);
            $table->string('administration')->nullable(); // Ex: DGI
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gel_declarations');
    }
};
