<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cost centers (analytical accounting)
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('code', 20);
            $table->string('name', 255);
            $table->enum('type', ['department', 'project', 'product', 'region'])->default('department');
            $table->foreignId('parent_id')->nullable()->constrained('cost_centers');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['client_id', 'code']);
        });

        // Analytical lines linked to journal entries
        Schema::create('analytic_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_line_id')->constrained('accounting_journal_lines')->cascadeOnDelete();
            $table->foreignId('cost_center_id')->constrained('cost_centers');
            $table->decimal('percentage', 5, 2)->default(100.00);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytic_lines');
        Schema::dropIfExists('cost_centers');
    }
};
