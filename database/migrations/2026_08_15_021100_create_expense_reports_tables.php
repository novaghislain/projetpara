<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('title');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_report_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_report_id')->constrained('expense_reports')->onDelete('cascade');
            $table->date('date');
            $table->string('category')->nullable();
            $table->string('merchant')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->string('receipt_path')->nullable();
            $table->json('ocr_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_report_lines');
        Schema::dropIfExists('expense_reports');
    }
};
