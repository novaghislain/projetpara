<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->string('reference', 50)->unique();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->foreignId('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $t->foreignId('business_user_id')->nullable()->constrained('business_users')->nullOnDelete();
            $t->string('customer_name')->nullable();
            $t->string('customer_phone')->nullable();
            $t->string('customer_email')->nullable();
            $t->decimal('subtotal', 12, 2)->default(0);
            $t->decimal('discount', 12, 2)->default(0);
            $t->string('discount_type', 20)->default('fixed');
            $t->decimal('total_ht', 12, 2)->default(0);
            $t->decimal('total_ttc', 12, 2)->default(0);
            $t->decimal('tax_amount', 12, 2)->default(0);
            $t->decimal('paid_amount', 12, 2)->default(0);
            $t->decimal('change_amount', 12, 2)->default(0);
            $t->string('status', 20)->default('completed');
            $t->string('payment_method', 50)->nullable();
            $t->string('emecef_uid')->nullable();
            $t->text('emecef_response')->nullable();
            $t->string('qr_code')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('sales'); }
};
