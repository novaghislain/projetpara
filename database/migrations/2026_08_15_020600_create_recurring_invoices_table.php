<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->string('frequency')->default('monthly'); // daily, weekly, monthly, yearly
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('XOF');
            $table->text('description')->nullable();
            $table->date('next_invoice_date');
            $table->string('status')->default('active'); // active, paused, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};
