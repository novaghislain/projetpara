<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('rh_employees')->cascadeOnDelete();
            $table->string('categorie');
            $table->decimal('montant', 15, 2);
            $table->text('description')->nullable();
            $table->string('justificatif_url')->nullable();
            $table->enum('statut', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('approbateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('date_approbation')->nullable();
            $table->dateTime('date_paiement')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_expenses');
    }
};
