<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gel_messages')) {
            Schema::create('gel_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('sender_id')->nullable();
                $table->string('sender_type')->default('user');
                $table->uuid('receiver_id')->nullable();
                $table->string('channel')->default('interne_comptable');
                $table->text('message');
                $table->string('piece_jointe')->nullable();
                $table->boolean('est_lu')->default(false);
                $table->unsignedBigInteger('portal_contact_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coordination_events')) {
            Schema::create('coordination_events', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->uuid('actor_id')->nullable();
                $table->string('actor_role')->nullable();
                $table->string('actor_name')->nullable();
                $table->uuid('recipient_id')->nullable();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('document_id')->nullable();
                $table->unsignedBigInteger('task_id')->nullable();
                $table->unsignedBigInteger('message_id')->nullable();
                $table->string('subject')->nullable();
                $table->text('body')->nullable();
                $table->string('link')->nullable();
                $table->string('icon')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('assigned_to')->nullable();
                $table->uuid('created_by')->nullable();
                $table->string('titre');
                $table->text('description')->nullable();
                $table->string('priorite')->default('normale');
                $table->string('statut')->default('a_faire');
                $table->date('date_echeance')->nullable();
                $table->timestamp('termine_at')->nullable();
                $table->string('source')->nullable();
                $table->string('coordination_type')->nullable();
                $table->unsignedBigInteger('related_document_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->unsignedBigInteger('folder_id')->nullable();
                $table->string('category')->nullable();
                $table->integer('annee_liee')->nullable();
                $table->integer('mois_lie')->nullable();
                $table->date('document_date')->nullable();
                $table->string('name');
                $table->string('original_name')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_hash')->nullable();
                $table->string('file_type')->nullable();
                $table->bigInteger('file_size')->default(0);
                $table->string('mime_type')->nullable();
                $table->text('description')->nullable();
                $table->json('tags')->nullable();
                $table->integer('version')->default(1);
                $table->boolean('is_favorite')->default(false);
                $table->string('privacy_level')->default('private');
                $table->string('share_token')->nullable();
                $table->uuid('uploaded_by')->nullable();
                $table->boolean('is_archived')->default(false);
                $table->string('workflow_step')->nullable();
                $table->text('workflow_notes')->nullable();
                $table->string('priority')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->uuid('processed_by')->nullable();
                $table->timestamp('transmitted_at')->nullable();
                $table->uuid('transmitted_by')->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->uuid('validated_by')->nullable();
                $table->boolean('is_secured')->default(false);
                $table->string('secure_password')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('coordination_events');
        Schema::dropIfExists('gel_messages');
    }
};
