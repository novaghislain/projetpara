<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gdpr_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->string('consent_type'); // terms, marketing, data_processing
            $table->boolean('is_granted')->default(false);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'consent_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_consents');
    }
};
