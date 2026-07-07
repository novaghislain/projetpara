<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // customer, supplier, both
            $table->string('code', 30)->nullable(); // Code client/fournisseur
            $table->string('company_name', 255)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('tax_id', 50)->nullable(); // N° IFU / RCCM
            $table->string('rccm', 50)->nullable();  // Registre du commerce
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->integer('payment_term_days')->default(30);
            $table->string('payment_method', 50)->nullable(); // transfert, cheques, especes
            $table->text('notes')->nullable();
            $table->string('iban', 50)->nullable();
            $table->string('swift', 20)->nullable();
            $table->string('status', 20)->default('active'); // active, inactive, blocked
            $table->foreignId('account_receivable_id')->nullable()->constrained('accounting_accounts');
            $table->foreignId('account_payable_id')->nullable()->constrained('accounting_accounts');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'code']);
            $table->index(['client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
