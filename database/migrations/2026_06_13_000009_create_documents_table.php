<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('client_folders')->nullOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // pdf, docx, xlsx, img, etc.
            $table->bigInteger('file_size')->nullable(); // en octets
            $table->string('mime_type')->nullable();
            $table->text('description')->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
