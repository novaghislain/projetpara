<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->unsignedBigInteger('journal_id')->nullable()->index();
                $table->unsignedBigInteger('fiscal_period_id')->nullable()->index();
                $table->string('entry_number')->nullable();
                $table->date('entry_date');
                $table->date('value_date')->nullable();
                $table->string('reference')->nullable();
                $table->text('description')->nullable();
                $table->decimal('total_debit', 15, 2)->default(0);
                $table->decimal('total_credit', 15, 2)->default(0);
                $table->boolean('is_balanced')->default(false);
                $table->string('status')->default('draft'); // draft, posted, locked, cancelled
                $table->boolean('classified_by_ai')->default(false);
                $table->uuid('created_by')->nullable();
                $table->uuid('validated_by')->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('entry_lines')) {
            Schema::create('entry_lines', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->unsignedBigInteger('entry_id')->index();
                $table->integer('line_number')->nullable();
                $table->unsignedBigInteger('account_id')->nullable()->index();
                $table->string('account_code')->nullable();
                $table->string('account_label')->nullable();
                $table->text('description')->nullable();
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('currency')->default('EUR');
                $table->decimal('exchange_rate', 10, 4)->default(1);
                $table->unsignedBigInteger('partner_id')->nullable();
                $table->string('partner_type')->nullable();
                $table->unsignedBigInteger('lettering_id')->nullable();
                $table->string('vat_code')->nullable();
                $table->decimal('vat_base', 15, 2)->nullable();
                $table->decimal('vat_amount', 15, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
