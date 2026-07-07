<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('bank_name', 255)->nullable();
            $table->string('account_number', 50);
            $table->string('iban', 50)->nullable();
            $table->string('swift', 20)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->string('type', 30)->default('checking'); // checking, savings, cash
            $table->foreignId('accounting_account_id')->constrained('accounting_accounts');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_date')->nullable();
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('reconciled_balance', 15, 2)->default(0);
            $table->date('last_reconciliation_date')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'account_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
