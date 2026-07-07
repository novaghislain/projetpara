<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('statut', 50)->default('active');
            $table->string('contexte', 100)->nullable();
            $table->string('source', 50)->default('gel');
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropForeign(['cabinet_id']);
            $table->dropColumn(['cabinet_id', 'statut', 'contexte', 'source', 'metadata']);
        });
    }
};
