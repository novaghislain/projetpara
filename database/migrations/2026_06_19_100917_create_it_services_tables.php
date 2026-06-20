<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. IT SLA Policies (must be first — referenced by tickets)
        Schema::create('it_sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->integer('first_response_hours')->default(8);
            $table->integer('resolution_hours')->default(48);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 2. IT Tickets (Helpdesk)
        Schema::create('it_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('ticket_number', 20)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('type', ['incident', 'request', 'change', 'problem'])->default('incident');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
            $table->string('category', 100)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('sla_id')->nullable()->constrained('it_sla_policies');
            $table->timestamp('sla_due_at')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->text('resolution')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('billable')->default(true);
            $table->decimal('billed_hours', 5, 2)->default(0);
            $table->timestamps();
        });

        // 3. IT Ticket Comments
        Schema::create('it_ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('it_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        // 4. IT Assets (ITAM)
        Schema::create('it_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('asset_tag', 50);
            $table->string('name', 255);
            $table->enum('category', ['computer', 'server', 'printer', 'network', 'mobile', 'software', 'other']);
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->enum('status', ['active', 'inactive', 'in_repair', 'disposed'])->default('active');
            $table->foreignId('assigned_to_user')->nullable()->constrained('users');
            $table->string('location', 255)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('warranty_expires_at')->nullable();
            $table->date('next_maintenance_at')->nullable();
            $table->string('os_version', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->text('notes')->nullable();
            $table->string('photo', 500)->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'asset_tag']);
        });

        // 5. IT Asset Licenses
        Schema::create('it_asset_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->nullable()->constrained('it_assets')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('software_name', 255);
            $table->string('license_key', 500)->nullable();
            $table->integer('seats')->default(1);
            $table->date('expires_at')->nullable();
            $table->string('vendor', 255)->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. IT Asset Interventions
        Schema::create('it_asset_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('it_assets');
            $table->foreignId('ticket_id')->nullable()->constrained('it_tickets');
            $table->foreignId('technician_id')->constrained('users');
            $table->enum('type', ['maintenance', 'repair', 'upgrade', 'installation', 'audit']);
            $table->date('date');
            $table->integer('duration_minutes')->nullable();
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->timestamps();
        });

        // 7. IT Maintenance Contracts
        Schema::create('it_maintenance_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('reference', 50);
            $table->string('title', 255);
            $table->enum('type', ['corrective', 'preventive', 'full_service', 'hotline']);
            $table->enum('status', ['active', 'expired', 'suspended'])->default('active');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_amount', 12, 2)->nullable();
            $table->integer('included_hours')->default(0);
            $table->integer('response_time_hours')->default(24);
            $table->string('coverage_hours', 50)->default('8h-18h');
            $table->json('covered_assets')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 8. IT Knowledge Base
        Schema::create('it_knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('category', 100)->nullable();
            $table->longText('content');
            $table->json('tags')->nullable();
            $table->boolean('is_public')->default(false);
            $table->integer('views')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_knowledge_base');
        Schema::dropIfExists('it_maintenance_contracts');
        Schema::dropIfExists('it_asset_interventions');
        Schema::dropIfExists('it_asset_licenses');
        Schema::dropIfExists('it_assets');
        Schema::dropIfExists('it_ticket_comments');
        Schema::dropIfExists('it_tickets');
        Schema::dropIfExists('it_sla_policies');
    }
};
