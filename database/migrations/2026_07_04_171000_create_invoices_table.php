<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // customer_invoice, supplier_invoice, credit_note, debit_note
            $table->string('invoice_number', 50); // FAC-2026-00001
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('partner_name', 255); // Dénormalisé pour archivage
            $table->string('partner_tax_id', 50)->nullable();
            $table->string('partner_address', 500)->nullable();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->date('delivery_date')->nullable();
            $table->string('payment_term', 100)->nullable(); // "30 jours", "comptant"
            $table->string('payment_method', 50)->nullable();
            $table->string('status', 30)->default('draft');
                // draft, sent, confirmed, partially_paid, paid, overdue, cancelled, credit_note
            $table->foreignId('related_invoice_id')->nullable()->constrained('invoices');
            $table->string('currency', 3)->default('XOF');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_base', 15, 2)->default(0);
            $table->decimal('vat_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'invoice_number']);
            $table->index(['client_id', 'type', 'status']);
            $table->index(['client_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
