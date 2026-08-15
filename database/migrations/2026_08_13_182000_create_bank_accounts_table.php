<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('iban')->nullable();
                $table->string('swift')->nullable();
                $table->string('currency')->default('FCFA');
                $table->string('type')->default('banque'); // banque, caisse, etc.
                
                $table->unsignedBigInteger('accounting_account_id')->nullable()->index();
                
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->date('opening_date')->nullable();
                
                $table->decimal('current_balance', 15, 2)->default(0);
                $table->decimal('reconciled_balance', 15, 2)->default(0);
                $table->date('last_reconciliation_date')->nullable();
                
                $table->string('contact_phone')->nullable();
                $table->string('contact_email')->nullable();
                $table->text('notes')->nullable();
                
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
