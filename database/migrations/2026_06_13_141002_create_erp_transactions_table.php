<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('type'); // income, expense, transfer
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->string('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_transactions'); }
};
