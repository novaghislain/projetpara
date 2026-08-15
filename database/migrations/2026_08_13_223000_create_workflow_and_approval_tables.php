<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gel_workflows')) {
            Schema::create('gel_workflows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
                $table->foreignUuid('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
                $table->string('nom');
                $table->string('type');
                $table->json('conditions')->nullable();
                $table->json('actions')->nullable();
                $table->boolean('actif')->default(true);
                $table->string('frequence')->nullable();
                $table->timestamp('dernier_execution')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('approval_workflows')) {
            Schema::create('approval_workflows', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->string('name');
                $table->string('trigger_model');
                $table->json('trigger_condition')->nullable();
                $table->json('steps');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('approval_requests')) {
            Schema::create('approval_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->integer('current_step')->default(0);
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->foreignUuid('requested_by')->constrained('utilisateurs')->cascadeOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('approval_step_logs')) {
            Schema::create('approval_step_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('request_id')->constrained('approval_requests')->cascadeOnDelete();
                $table->integer('step_number');
                $table->foreignUuid('approver_id')->constrained('utilisateurs')->cascadeOnDelete();
                $table->string('action'); // approved, rejected
                $table->text('comment')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_step_logs');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('gel_workflows');
    }
};
