<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreignId('fiscal_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bank_account', 50); // compte banque (code comptable)
            $table->string('bank_name', 255)->nullable();
            $table->string('period', 7); // YYYY-MM
            $table->date('statement_date');
            // Soldes
            $table->decimal('balance_per_statement', 15, 2)->default(0);
            $table->decimal('balance_per_books', 15, 2)->default(0);
            $table->decimal('difference', 15, 2)->default(0);
            // Éléments de rapprochement
            $table->decimal('outstanding_deposits', 15, 2)->default(0);   // dépôts en cours
            $table->decimal('outstanding_checks', 15, 2)->default(0);     // chèques en circulation
            $table->decimal('bank_charges', 15, 2)->default(0);
            $table->decimal('interest_income', 15, 2)->default(0);
            $table->json('unmatched_items')->nullable(); // écritures non rapprochées
            $table->enum('status', ['draft', 'matched', 'approved'])->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'bank_account', 'period']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
    }
};
