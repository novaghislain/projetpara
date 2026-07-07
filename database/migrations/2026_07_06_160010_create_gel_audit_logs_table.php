<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->nullable()->constrained('gel_cabinets')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 100);
            $table->string('auditable_type', 255)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['cabinet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_audit_logs');
    }
};
