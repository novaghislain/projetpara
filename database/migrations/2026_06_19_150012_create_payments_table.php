<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $t->string('payment_method', 50);
            $t->decimal('amount', 12, 2);
            $t->string('reference')->nullable();
            $t->string('status', 20)->default('completed');
            $t->json('gateway_response')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
