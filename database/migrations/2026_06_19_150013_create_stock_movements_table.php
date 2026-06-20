<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $t->string('type', 20);
            $t->decimal('quantity', 10, 2);
            $t->decimal('stock_before', 10, 2)->default(0);
            $t->decimal('stock_after', 10, 2)->default(0);
            $t->string('reference_type')->nullable();
            $t->unsignedBigInteger('reference_id')->nullable();
            $t->string('motif')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['client_id', 'product_id']);
            $t->index(['reference_type', 'reference_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('stock_movements'); }
};
