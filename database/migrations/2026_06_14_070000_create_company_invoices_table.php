<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->enum('type', ['devis', 'facture', 'avoir'])->default('facture');
            $table->enum('status', ['brouillon', 'emise', 'payee', 'annulee', 'impayee'])->default('brouillon');
            $table->string('recipient_name');
            $table->text('recipient_address')->nullable();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_ht', 15, 2)->default(0);
            $table->decimal('total_tva', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('company_invoices')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total_ht', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('company_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('company_invoices')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['cash', 'transfer', 'momo', 'cheque'])->default('transfer');
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_payments');
        Schema::dropIfExists('company_invoice_items');
        Schema::dropIfExists('company_invoices');
    }
};
