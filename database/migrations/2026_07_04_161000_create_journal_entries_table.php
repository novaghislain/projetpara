<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_period_id')->constrained();
            $table->string('entry_number', 50);                // "AC-2026-00001"
            $table->date('entry_date');
            $table->date('value_date')->nullable();
            $table->string('reference', 100)->nullable();       // Numéro facture, etc.
            $table->text('description')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->boolean('is_balanced')->default(false);
            $table->string('status', 20)->default('draft');     // draft, posted, locked, cancelled
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('entry_number');
            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
