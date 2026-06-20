<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('dae_module'); // courriers, emails, agenda, etc.
            $table->string('action');      // create, update, delete, archiver, traiter, etc.
            $table->string('entity_type'); // App\Models\Dae\DaeCourrier...
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['dae_module', 'action']);
            $table->index('entity_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_audit_logs');
    }
};
