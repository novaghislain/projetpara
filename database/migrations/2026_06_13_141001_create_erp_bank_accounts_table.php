<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Caisse Principale, Compte BOA, Momo...
            $table->string('type')->default('cash'); // cash, bank, mobile_money
            $table->string('account_number')->nullable();
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_bank_accounts'); }
};
