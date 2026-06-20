<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_supplier', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $t->string('reference')->nullable();
            $t->decimal('price', 12, 2)->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_supplier'); }
};
