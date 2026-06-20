<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->string('name');
            $t->string('barcode', 100)->nullable();
            $t->decimal('price_ht', 12, 2)->default(0);
            $t->decimal('price_ttc', 12, 2)->default(0);
            $t->decimal('stock_qty', 10, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_variants'); }
};
