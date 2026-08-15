<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tva_declarations')) {
            Schema::create('tva_declarations', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->unsignedBigInteger('fiscal_year_id')->nullable()->index();
                $table->string('period')->nullable();
                $table->string('type')->nullable();
                $table->decimal('tva_collected', 15, 2)->default(0);
                $table->decimal('tva_deductible', 15, 2)->default(0);
                $table->decimal('tva_net', 15, 2)->default(0);
                $table->json('details')->nullable();
                $table->string('status')->default('draft');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->uuid('created_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tva_declarations');
    }
};
