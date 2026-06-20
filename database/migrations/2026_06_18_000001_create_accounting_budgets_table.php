<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['recette', 'depense', 'tresorerie', 'investissement'])->default('depense');
            $table->enum('status', ['brouillon', 'actif', 'verrouille', 'archive'])->default('brouillon');
            $table->decimal('montant_prevu', 18, 2)->default(0);
            $table->decimal('montant_realise', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'fiscal_year_id', 'type']);
            $table->index(['client_id', 'status']);
        });

        Schema::create('accounting_budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('accounting_budgets')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounting_accounts')->cascadeOnDelete();
            $table->string('label');
            $table->decimal('montant_prevu', 18, 2)->default(0);
            $table->decimal('montant_realise', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['budget_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_budget_lines');
        Schema::dropIfExists('accounting_budgets');
    }
};
