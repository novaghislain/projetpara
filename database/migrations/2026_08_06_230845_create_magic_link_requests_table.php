<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magic_link_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('requested_documents')->nullable();
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'all'])->default('email');
            $table->enum('status', ['sent', 'viewed', 'responded', 'expired'])->default('sent');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('response_files')->nullable();
            $table->text('response_message')->nullable();
            $table->timestamps();
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magic_link_requests');
    }
};
