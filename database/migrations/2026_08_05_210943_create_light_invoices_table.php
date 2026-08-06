<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('light_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->string('type'); // achat, vente
            $table->string('invoice_number');
            $table->string('partner_name');
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->decimal('amount_ht', 15, 2);
            $table->decimal('tva', 15, 2)->default(0);
            $table->decimal('amount_ttc', 15, 2);
            $table->string('status')->default('à_payer'); // à_payer, payé, en_retard
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('light_invoices');
    }
};
