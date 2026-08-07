<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workpapers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('fiscal_year_id')->nullable()->constrained('fiscal_years')->onDelete('set null');
            $table->string('period', 20); // ex: "2025", "2025-Q1"
            $table->foreignId('account_id')->constrained('accounting_accounts')->onDelete('cascade');
            $table->enum('status', ['not_reviewed', 'in_progress', 'reviewed'])->default('not_reviewed');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('adjustments')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'fiscal_year_id', 'account_id'], 'uk_client_year_account');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workpapers');
    }
};
