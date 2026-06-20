<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('rh_employees')->cascadeOnDelete();
            $table->string('periode', 7); // YYYY-MM
            $table->decimal('salaire_base', 15, 2);
            $table->json('primes')->nullable();
            $table->json('indemnites')->nullable();
            $table->json('cotisations')->nullable();
            $table->decimal('avance', 15, 2)->default(0);
            $table->decimal('net_a_payer', 15, 2);
            $table->json('retenues')->nullable();
            $table->dateTime('date_paiement')->nullable();
            $table->enum('statut', ['brouillon', 'calcule', 'valide', 'paye', 'annule'])->default('brouillon');
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'periode']);
            $table->index(['statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_payrolls');
    }
};
