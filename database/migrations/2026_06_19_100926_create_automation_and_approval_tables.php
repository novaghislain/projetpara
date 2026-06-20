<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Auto-reminder rules
        Schema::create('relance_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('name', 255);
            $table->integer('trigger_days');
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'all'])->default('email');
            $table->string('template_subject', 255)->nullable();
            $table->longText('template_body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Approval workflows
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('name', 255);
            $table->string('trigger_model', 100);
            $table->json('trigger_condition')->nullable();
            $table->json('steps');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Approval requests
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows');
            $table->string('model_type', 100);
            $table->unsignedBigInteger('model_id');
            $table->integer('current_step')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('requested_by')->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 4. Approval steps log
        Schema::create('approval_steps_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('approval_requests');
            $table->integer('step_number');
            $table->foreignId('approver_id')->constrained('users');
            $table->enum('action', ['approved', 'rejected', 'delegated']);
            $table->text('comment')->nullable();
            $table->timestamp('acted_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps_log');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('relance_rules');
    }
};
