<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inventory_session_id')->constrained('inventory_sessions')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->decimal('theoretical_qty', 10, 2);
            $t->decimal('actual_qty', 10, 2);
            $t->decimal('difference', 10, 2);
            $t->string('motif')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inventory_lines'); }
};
