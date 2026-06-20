<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_closing_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->string('reference', 50)->nullable();
            $table->enum('type', [
                'inventaire', 'amortissement', 'provision',
                'regularisation', 'resultat', 'affectation',
            ]);
            $table->text('description')->nullable();
            $table->json('entries')->nullable(); // [{account_id, debit, credit, label}]
            $table->enum('status', ['brouillon', 'valide', 'comptabilise'])->default('brouillon');
            $table->foreignId('journal_id')->nullable()->constrained('accounting_journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'reference'])->name('ce_cli_ref_idx');
            $table->index(['client_id', 'fiscal_year_id', 'type'])->name('ce_cli_fy_type_idx');
            $table->index(['client_id', 'status'])->name('ce_cli_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_closing_entries');
    }
};
