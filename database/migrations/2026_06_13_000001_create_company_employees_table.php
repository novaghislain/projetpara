<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->enum('contract_type', ['CDI', 'CDD', 'INTERIM', 'STAGE'])->default('CDI');
            $table->enum('status', ['active', 'suspended', 'left'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_id')->references('id')->on('client_folders')->onDelete('cascade');
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_employees');
    }
};
