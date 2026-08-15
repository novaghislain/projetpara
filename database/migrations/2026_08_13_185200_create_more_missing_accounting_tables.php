<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('accounting_accounts')) {
            Schema::create('accounting_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->uuid('client_id')->nullable()->index();
                $table->string('code')->index();
                $table->string('name');
                $table->string('type')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('syscohada_class')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->boolean('is_syscohada')->default(false);
                $table->decimal('tva_rate', 5, 2)->nullable();
                $table->boolean('has_tva')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fiscal_years')) {
            Schema::create('fiscal_years', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->integer('year');
                $table->date('date_start');
                $table->date('date_end');
                $table->string('status')->default('open');
                $table->timestamp('closed_at')->nullable();
                $table->uuid('closed_by')->nullable();
                $table->boolean('check_balance')->default(false);
                $table->boolean('check_tva')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fiscal_periods')) {
            Schema::create('fiscal_periods', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('fiscal_year_id')->index();
                $table->string('code');
                $table->string('label')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status')->default('open');
                $table->boolean('is_current')->default(false);
                $table->timestamp('closed_at')->nullable();
                $table->uuid('closed_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_periods');
        Schema::dropIfExists('fiscal_years');
        Schema::dropIfExists('accounting_accounts');
    }
};
