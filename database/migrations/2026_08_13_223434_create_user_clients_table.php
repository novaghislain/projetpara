<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // user_clients : association utilisateur <-> client (entreprise cliente)
        if (!Schema::hasTable('user_clients')) {
            Schema::create('user_clients', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->index();
                $table->uuid('client_id')->index();
                $table->string('role')->nullable();
                $table->boolean('is_active')->default(true);
                $table->uuid('invited_by')->nullable();
                $table->timestamp('joined_at')->nullable();
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamps();
            });
        }

        // client_invitations : invitations envoyées aux clients
        if (!Schema::hasTable('client_invitations')) {
            Schema::create('client_invitations', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('invited_by')->nullable()->index();
                $table->string('email')->nullable();
                $table->string('token')->nullable()->unique();
                $table->string('status')->default('pending');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // client_call_logs : journal des appels clients
        if (!Schema::hasTable('client_call_logs')) {
            Schema::create('client_call_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('direction')->default('sortant'); // entrant | sortant
                $table->string('contact_name')->nullable();
                $table->string('phone')->nullable();
                $table->text('notes')->nullable();
                $table->string('statut')->default('terminé');
                $table->timestamp('called_at')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->string('action_created')->nullable();
                $table->timestamps();
            });
        }

        // audit_logs : alias pour audit_trails simplifié
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('event')->nullable();
                $table->string('auditable_type')->nullable();
                $table->uuid('auditable_id')->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
            });
        }

        // document_templates : modèles de documents
        if (!Schema::hasTable('document_templates')) {
            Schema::create('document_templates', function (Blueprint $table) {
                $table->id();
                $table->uuid('entreprise_id')->nullable()->index();
                $table->string('nom');
                $table->string('type')->nullable();
                $table->text('contenu')->nullable();
                $table->json('variables')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        // tasks : tâches générales
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('created_by')->nullable()->index();
                $table->uuid('assigned_to')->nullable()->index();
                $table->string('titre');
                $table->text('description')->nullable();
                $table->string('statut')->default('a_faire');
                $table->string('priorite')->default('moyenne');
                $table->date('date_echeance')->nullable();
                $table->timestamp('termine_at')->nullable();
                $table->timestamps();
            });
        }

        // gel_messages : messages internes
        if (!Schema::hasTable('gel_messages')) {
            Schema::create('gel_messages', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('sender_id')->nullable()->index();
                $table->string('sender_type')->nullable(); // business | staff
                $table->text('contenu')->nullable();
                $table->boolean('est_lu')->default(false);
                $table->timestamps();
            });
        }

        // relance_rules : règles de relance automatique
        if (!Schema::hasTable('relance_rules')) {
            Schema::create('relance_rules', function (Blueprint $table) {
                $table->id();
                $table->uuid('entreprise_id')->nullable()->index();
                $table->string('nom');
                $table->string('type')->nullable();
                $table->integer('delai_jours')->default(0);
                $table->text('message_template')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        // notifications : notifications utilisateurs
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->string('notifiable_type');
                $table->uuid('notifiable_id')->index();
                $table->json('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // coordination_events : fil de coordination secrétaire <-> comptable
        if (!Schema::hasTable('coordination_events')) {
            // Already exists, skip
        }

        // reservations : réservations de salles / ressources
        if (!Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('titre')->nullable();
                $table->string('ressource')->nullable();
                $table->string('statut')->default('en_attente');
                $table->timestamp('debut_at')->nullable();
                $table->timestamp('fin_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // business_trips : voyages d'affaires
        if (!Schema::hasTable('business_trips')) {
            Schema::create('business_trips', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('destination')->nullable();
                $table->string('motif')->nullable();
                $table->date('date_depart')->nullable();
                $table->date('date_retour')->nullable();
                $table->string('statut')->default('planifie');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_clients');
        Schema::dropIfExists('client_invitations');
        Schema::dropIfExists('client_call_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('gel_messages');
        Schema::dropIfExists('relance_rules');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('business_trips');
    }
};
