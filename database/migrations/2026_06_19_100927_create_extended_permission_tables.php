<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Field-level permission restrictions
        Schema::create('permission_field_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions');
            $table->string('model_type', 100);
            $table->json('hidden_fields');
            $table->timestamps();
        });

        // 2. Temporary permission delegations
        Schema::create('permission_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegator_id')->constrained('users');
            $table->foreignId('delegate_id')->constrained('users');
            $table->json('permissions');
            $table->text('reason')->nullable();
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_delegations');
        Schema::dropIfExists('permission_field_restrictions');
    }
};
