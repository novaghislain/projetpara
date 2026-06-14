<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['encaissement', 'decaissement']);
            $table->string('category')->nullable();
            $table->string('payment_method', 50)->default('especes');
            $table->decimal('amount', 15, 2);
            $table->string('reference', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            // Polymorphe : lié à une facture, un client, etc.
            $table->nullableMorphs('transactional');
            $table->boolean('is_reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
