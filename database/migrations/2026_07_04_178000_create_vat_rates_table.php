<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10); // T, R, S, N, E, SP
            $table->string('name', 100); // "TVA Normale (18%)", "TVA Réduite (9%)", "Exonéré"
            $table->decimal('rate', 5, 2)->default(0); // 18.00, 9.00, 0.00
            $table->string('type', 20)->default('standard'); // standard, reduced, super_reduced, zero, exempt, special
            $table->foreignId('collect_account_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->foreignId('deduct_account_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('country_code', 3)->nullable(); // BJ, CI, SN, etc.
            $table->timestamps();

            $table->unique(['client_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_rates');
    }
};
