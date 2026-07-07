<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number', 50); // TVA-2026-03-001
            $table->string('period_type', 20); // monthly, quarterly, yearly
            $table->integer('year');
            $table->integer('month')->nullable();
            $table->integer('quarter')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('due_date');
            $table->date('payment_date')->nullable();

            // Collecte
            $table->decimal('vat_collected_normal', 15, 2)->default(0);
            $table->decimal('vat_collected_reduced', 15, 2)->default(0);
            $table->decimal('vat_collected_other', 15, 2)->default(0);
            $table->decimal('vat_collected_total', 15, 2)->default(0);

            // Déductible
            $table->decimal('vat_deductible_normal', 15, 2)->default(0);
            $table->decimal('vat_deductible_reduced', 15, 2)->default(0);
            $table->decimal('vat_deductible_immobilisations', 15, 2)->default(0);
            $table->decimal('vat_deductible_other', 15, 2)->default(0);
            $table->decimal('vat_deductible_total', 15, 2)->default(0);

            // Résultat
            $table->decimal('vat_net', 15, 2)->default(0);
            $table->decimal('vat_payable', 15, 2)->default(0);
            $table->decimal('vat_credit', 15, 2)->default(0);
            $table->decimal('previous_credit', 15, 2)->default(0);
            $table->decimal('net_to_pay', 15, 2)->default(0);

            // Statuts
            $table->string('status', 20)->default('draft'); // draft, computed, submitted, paid, cancelled
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('payment_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'declaration_number']);
            $table->index(['client_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_declarations');
    }
};
