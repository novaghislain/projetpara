<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_suggestions')) {
            Schema::create('ai_suggestions', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('agent')->default('AI');
                $table->string('type')->default('optimization');
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('data')->nullable();
                $table->json('metadata')->nullable();
                $table->string('status')->default('pending');
                $table->uuid('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_suggestions');
    }
};
