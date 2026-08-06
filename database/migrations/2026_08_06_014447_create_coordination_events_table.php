<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fil d'activité de coordination Secrétaire ↔ Comptable sur une entreprise.
 *
 * Chaque interaction entre les deux professionnels d'une même entreprise
 * (transmission de document, demande de document, note liée, confirmation de
 * traitement, alerte, message de coordination) est journalisée ici, en
 * français, et alimente :
 *  - la vue partagée « Activité de coordination » (S1.2 / S4.3),
 *  - l'Historique consultable (lecture seule) par l'Administrateur d'Entreprise (S1.4).
 *
 * La portée est strictement le client_id (gel_clients.id) de l'entreprise commune.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coordination_events', function (Blueprint $table) {
            $table->id();

            // Entreprise commune (gel_clients.id) — portée stricte de la coordination
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('cabinet_id')->nullable();

            // Auteur de l'action et destinataire (si ciblé)
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_role', 40)->nullable();      // secretaire | comptable | admin_entreprise
            $table->string('actor_name')->nullable();          // nom figé à l'écriture (pérennité historique)
            $table->unsignedBigInteger('recipient_id')->nullable();

            // Type d'événement de coordination
            // document_transmis | document_demande | note_liee | confirmation_traitement | alerte | message_coordination | demandes_infos
            $table->string('type', 40)->index();

            // Références optionnelles (pour ouvrir la ressource concernée)
            $table->unsignedBigInteger('document_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('message_id')->nullable();

            // Libellés en français (consultables par l'Admin Entreprise, lecture seule)
            $table->string('subject', 255);
            $table->text('body')->nullable();
            $table->string('link', 500)->nullable();
            $table->string('icon', 60)->nullable();

            $table->timestamps();

            $table->index(['client_id', 'created_at']);
            $table->foreign('client_id')->references('id')->on('gel_clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordination_events');
    }
};
