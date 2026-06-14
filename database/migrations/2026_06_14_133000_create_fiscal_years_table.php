<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->integer('year');
            $table->date('date_start');
            $table->date('date_end');
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');
            // Clôture
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            // Checklist clôture
            $table->boolean('check_balance')->default(false);
            $table->boolean('check_tva')->default(false);
            $table->boolean('check_cnss')->default(false);
            $table->boolean('check_reconciliation')->default(false);
            $table->boolean('check_inventory')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'year']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_years');
    }
};
