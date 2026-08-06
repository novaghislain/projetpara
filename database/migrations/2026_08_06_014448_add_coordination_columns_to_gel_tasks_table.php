<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colonnes de coordination sur gel_tasks (S2.2 / S3.1 / S3.3 et badge S4.2).
 *
 * - source            : 'client' par défaut ; 'coordination' quand la tâche est
 *                       une demande inter-personnel (secrétaire ↔ comptable).
 * - coordination_type : sous-type (demande_document | note | alerte | tache).
 * - related_document_id : document auquel la demande/note est rattachée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gel_tasks', function (Blueprint $table) {
            $table->string('source')->default('client')->after('statut');
            $table->string('coordination_type')->nullable()->after('source');
            $table->unsignedBigInteger('related_document_id')->nullable()->after('coordination_type');
        });
    }

    public function down(): void
    {
        Schema::table('gel_tasks', function (Blueprint $table) {
            $table->dropColumn(['source', 'coordination_type', 'related_document_id']);
        });
    }
};