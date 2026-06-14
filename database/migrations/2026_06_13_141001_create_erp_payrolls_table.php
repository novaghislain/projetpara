<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_employee_id')->constrained()->cascadeOnDelete();
            $table->string('period'); // e.g., 2026-04
            $table->decimal('base_salary', 15, 2);
            $table->decimal('bonuses', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('advances', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->string('status')->default('draft'); // draft, validated, paid
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_payrolls'); }
};
