<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['scheduled', 'reminder', 'template'])->default('scheduled');
            $table->string('transaction_type', 50); // journal_entry, invoice, expense, credit_note
            $table->string('title');
            $table->json('template_data'); // données du modèle (lignes d'écriture, montant, etc.)
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'yearly'])->nullable();
            $table->date('next_occurrence')->nullable();
            $table->date('last_occurrence')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('occurrences_count')->default(0);
            $table->integer('max_occurrences')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
