<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_trails')) {
            Schema::create('audit_trails', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('event');
                $table->string('auditable_type')->nullable();
                $table->uuid('auditable_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_resolved')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->index();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->string('title')->nullable();
                $table->json('messages')->nullable();
                $table->string('statut')->default('active');
                $table->string('contexte')->nullable();
                $table->string('source')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('audit_trails');
    }
};
