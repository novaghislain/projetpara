<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journal_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('journal_type', 20);
            $table->unsignedBigInteger('fiscal_year_id')->nullable();
            $table->integer('last_number')->default(0);
            $table->string('prefix', 10)->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'journal_type', 'fiscal_year_id'], 'acc_jour_seq_unq');
            $table->index(['client_id', 'journal_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_sequences');
    }
};
