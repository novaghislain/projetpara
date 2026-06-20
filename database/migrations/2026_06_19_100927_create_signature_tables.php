<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents');
            $table->string('signer_name', 255);
            $table->string('signer_email', 255)->nullable();
            $table->string('signer_phone', 20)->nullable();
            $table->longText('signature_data')->nullable(); // base64 image
            $table->string('document_hash', 64); // SHA256
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('signed_at')->useCurrent();
            $table->string('token', 100)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
