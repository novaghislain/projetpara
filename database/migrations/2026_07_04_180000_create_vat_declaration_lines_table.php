<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_declaration_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('declaration_id')->constrained('vat_declarations')->cascadeOnDelete();
            $table->string('type', 20); // collected, deductible
            $table->string('vat_code', 10); // T, R, S, N
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('base_amount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->integer('invoice_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_declaration_lines');
    }
};
