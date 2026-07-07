<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->integer('line_number');
            $table->foreignId('account_id')->constrained('accounting_accounts');
            $table->string('account_code', 20);
            $table->string('account_label', 255);
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->foreignId('partner_id')->nullable()->constrained('clients');
            $table->string('partner_type', 50)->nullable();     // customer, supplier, employee, other
            $table->string('lettering_id', 50)->nullable();     // Pour lettrage
            $table->string('vat_code', 20)->nullable();
            $table->decimal('vat_base', 15, 2)->nullable();
            $table->decimal('vat_amount', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('entry_id');
            $table->index('account_id');
            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_lines');
    }
};
