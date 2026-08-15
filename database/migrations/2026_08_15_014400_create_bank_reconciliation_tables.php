<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->date('statement_date');
            $table->decimal('starting_balance', 15, 2)->default(0);
            $table->decimal('ending_balance', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('label');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamps();
        });

        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_line_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_reconciled', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statements');
    }
};
