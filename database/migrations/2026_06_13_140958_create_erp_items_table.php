<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('designation');
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->integer('stock_alert')->default(0);
            $table->string('unit')->default('unite');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_items'); }
};
