<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->nullable()->constrained('gel_clients')->onDelete('cascade');
            $table->foreignUuid('sender_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignUuid('receiver_id')->nullable()->constrained('utilisateurs')->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
