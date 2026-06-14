<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_legal_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('title');
            $table->string('reference');
            $table->string('type'); // contentieux, consultation, conseil, autre
            $table->string('status')->default('open'); // open, in_progress, closed, archived
            $table->text('description')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->date('start_date');
            $table->date('resolution_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_legal_cases');
    }
};
