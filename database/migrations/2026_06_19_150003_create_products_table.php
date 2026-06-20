<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->string('brand')->nullable();
            $t->string('barcode', 100)->nullable()->unique();
            $t->string('sku', 100)->nullable();
            $t->decimal('price_ht', 12, 2)->default(0);
            $t->decimal('price_ttc', 12, 2)->default(0);
            $t->decimal('price_purchase', 12, 2)->default(0);
            $t->decimal('tva_rate', 5, 2)->default(0);
            $t->string('unit', 20)->default('piece');
            $t->decimal('stock_qty', 10, 2)->default(0);
            $t->decimal('stock_alert', 10, 2)->default(0);
            $t->decimal('stock_critical', 10, 2)->default(0);
            $t->string('location')->nullable();
            $t->boolean('is_active')->default(true);
            $t->boolean('is_bundle')->default(false);
            $t->timestamps();
            $t->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
