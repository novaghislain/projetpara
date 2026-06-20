<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('model_type', 100);
            $table->unsignedBigInteger('model_id');
            $table->string('action', 100);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'model_type', 'model_id'], 'laudit_cli_model_idx');
            $table->index(['client_id', 'created_at'], 'laudit_cli_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_audit_log');
    }
};
