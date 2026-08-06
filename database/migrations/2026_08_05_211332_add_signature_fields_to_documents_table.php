<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('signature_status')->default('non_signé')->after('is_archived');
            $table->string('signature_provider_id')->nullable()->after('signature_status');
            $table->date('expiration_date')->nullable()->after('signature_provider_id');
            $table->boolean('requires_signature')->default(false)->after('expiration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['signature_status', 'signature_provider_id', 'expiration_date', 'requires_signature']);
        });
    }
};
