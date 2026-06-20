<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $t->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $t->string('product_name');
            $t->string('barcode', 100)->nullable();
            $t->decimal('quantity', 10, 2)->default(1);
            $t->decimal('unit_price_ht', 12, 2)->default(0);
            $t->decimal('unit_price_ttc', 12, 2)->default(0);
            $t->decimal('discount', 12, 2)->default(0);
            $t->decimal('total_ht', 12, 2)->default(0);
            $t->decimal('total_ttc', 12, 2)->default(0);
            $t->decimal('tva_rate', 5, 2)->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sale_items'); }
};
