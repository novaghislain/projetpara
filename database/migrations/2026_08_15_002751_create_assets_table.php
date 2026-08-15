<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->string('name');
            $table->date('purchase_date');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->integer('useful_life_years');
            $table->string('depreciation_method')->default('linear');
            $table->unsignedBigInteger('asset_account_id')->nullable();
            $table->unsignedBigInteger('depreciation_account_id')->nullable();
            $table->unsignedBigInteger('expense_account_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
