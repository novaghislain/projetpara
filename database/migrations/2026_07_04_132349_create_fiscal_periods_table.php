<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('label', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');
            $table->boolean('is_current')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['fiscal_year_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_periods');
    }
};
