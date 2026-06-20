<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('document_id')->nullable()->index();
            $table->string('original_filename');
            $table->string('mime_type');
            $table->string('file_path');
            $table->longText('extracted_text')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->json('entities')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_scans');
    }
};
