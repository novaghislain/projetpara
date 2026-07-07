<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->date('value_date')->nullable();
            $table->string('description', 500);
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('reference', 100)->nullable();
            $table->string('cheque_number', 50)->nullable();
            $table->string('category', 50)->nullable(); // income, expense, transfer
            $table->string('status', 20)->default('cleared'); // pending, cleared, reconciled, flagged
            $table->boolean('is_reconciled')->default(false);
            $table->boolean('is_imported')->default(false);
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'bank_account_id', 'transaction_date'], 'bk_txn_ca_ba_txn_idx');
            $table->index(['client_id', 'bank_account_id', 'status'], 'bk_txn_ca_ba_st_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
