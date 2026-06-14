<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('erp_warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // entry, exit, transfer
            $table->integer('quantity');
            $table->string('reference_doc')->nullable(); // Bon d'arrivage, Facture, etc.
            $table->date('movement_date');
            $table->text('motif')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_stock_movements'); }
};
