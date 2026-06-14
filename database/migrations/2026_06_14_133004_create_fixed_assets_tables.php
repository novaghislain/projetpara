<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreignId('fiscal_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('designation');
            $table->string('category', 50)->default('other'); // informatique, mobilier, vehicule, batiment, logiciel, other
            $table->date('acquisition_date');
            $table->decimal('gross_value', 15, 2);
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->integer('depreciation_months'); // durée d'amortissement en mois
            $table->enum('depreciation_method', ['linear', 'declining'])->default('linear');
            $table->decimal('net_book_value', 15, 2)->default(0);
            $table->string('account_code', 20)->nullable(); // compte d'immobilisation
            $table->string('depreciation_account_code', 20)->nullable(); // compte d'amortissement
            $table->string('status', 20)->default('active'); // active, fully_depreciated, sold, scrapped
            // Cession
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_price', 15, 2)->nullable();
            $table->decimal('capital_gain_loss', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });

        Schema::create('depreciation_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('fiscal_year_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('period_number'); // 1 to depreciation_months
            $table->date('period_date');
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2);
            $table->decimal('net_value', 15, 2);
            $table->boolean('is_booked')->default(false); // écriture comptable générée ?
            $table->foreignId('journal_id')->nullable()->constrained('accounting_journals')->nullOnDelete();
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depreciation_schedules');
        Schema::dropIfExists('fixed_assets');
    }
};
