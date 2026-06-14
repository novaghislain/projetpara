<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('erp_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('designation');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_invoice_items'); }
};
