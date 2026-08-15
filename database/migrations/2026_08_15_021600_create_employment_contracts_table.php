<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->string('employee_name');
            $table->string('position');
            $table->string('contract_type')->default('CDI');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('gross_salary', 15, 2);
            $table->longText('content_html')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};
