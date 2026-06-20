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
        // Drop first if the earlier extended_permission_tables migration created it
        Schema::dropIfExists('permission_field_restrictions');

        Schema::create('permission_field_restrictions', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50);
            $table->string('action', 50);
            $table->string('role_slug', 50)->nullable()->comment('Null = applies to all roles with this module+action');
            $table->json('hidden_fields')->comment('Fields hidden from this role for this module action');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['module', 'action']);
            $table->index(['module', 'role_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_field_restrictions');
    }
};
