<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Partners (clients & fournisseurs du cabinet comptable) ───
        if (!Schema::hasTable('partners')) {
            Schema::create('partners', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable()->index(); // FK vers clients
                $table->enum('type', ['customer', 'vendor', 'both'])->default('customer');
                $table->string('company_name')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('tax_id')->nullable(); // IFU
                $table->string('trade_register')->nullable(); // RCCM
                $table->enum('status', ['actif', 'inactif'])->default('actif');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── Products / Services ───
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('sku')->nullable();
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('purchase_price', 15, 2)->default(0);
                $table->decimal('vat_rate', 5, 2)->default(18); // Taux TVA par défaut Bénin
                $table->enum('type', ['product', 'service'])->default('service');
                $table->string('unit')->nullable(); // unité : h, kg, pièce, etc.
                $table->enum('status', ['actif', 'inactif'])->default('actif');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── Invoices (factures, bons de commande, devis, etc.) ───
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->unsignedBigInteger('partner_id')->nullable()->index();
                $table->string('partner_name')->nullable(); // dénormalisé
                $table->enum('type', [
                    'customer_invoice',   // Facture client
                    'customer_credit',    // Note de crédit client
                    'customer_quote',     // Devis
                    'sales_order',        // Commande de vente
                    'sales_receipt',      // Récépissé de vente
                    'purchase_order',     // Bon de commande fournisseur
                    'vendor_bill',        // Facture fournisseur
                    'vendor_credit',      // Note de crédit fournisseur
                    'expense',            // Dépense
                ])->default('customer_invoice');
                $table->string('invoice_number')->nullable()->unique();
                $table->date('invoice_date');
                $table->date('due_date')->nullable();
                $table->date('delivery_date')->nullable();
                $table->string('payment_term')->nullable();
                $table->enum('status', [
                    'draft', 'sent', 'approved', 'overdue', 'paid',
                    'partial', 'cancelled', 'refunded'
                ])->default('draft');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->decimal('amount_paid', 15, 2)->default(0);
                $table->decimal('balance_due', 15, 2)->default(0);
                $table->string('currency', 10)->default('FCFA');
                $table->text('notes')->nullable();
                $table->text('terms_conditions')->nullable();
                // Certification eMecef
                $table->string('mecef_number')->nullable();
                $table->string('mecef_counter')->nullable();
                $table->string('mecef_qr_url')->nullable();
                $table->timestamp('mecef_certified_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── Invoice Lines ───
        if (!Schema::hasTable('invoice_lines')) {
            Schema::create('invoice_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->text('description');
                $table->decimal('quantity', 12, 3)->default(1);
                $table->string('unit')->nullable();
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('vat_rate', 5, 2)->default(18);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('vat_amount', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            });
        }

        // ─── Payments (encaissements & paiements) ───
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->unsignedBigInteger('invoice_id')->nullable()->index();
                $table->unsignedBigInteger('partner_id')->nullable();
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->enum('method', ['cash', 'bank', 'mobile_money', 'cheque', 'other'])->default('bank');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('partners');
    }
};
