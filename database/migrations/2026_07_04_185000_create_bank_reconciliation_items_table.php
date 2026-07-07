<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reconciliation_id')->constrained('bank_reconciliations')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('bank_transactions')->cascadeOnDelete();
            $table->string('type', 20); // matched, unmatched, missing_in_books, missing_in_statement
            $table->string('status', 20)->default('pending'); // pending, cleared, exception
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['reconciliation_id', 'transaction_id'], 'bk_recit_rec_txn_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_items');
    }
};
