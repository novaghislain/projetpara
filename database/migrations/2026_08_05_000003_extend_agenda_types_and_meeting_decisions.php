<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S7 : élargir le type d'événement d'agenda aux échéances fiscales / CNSS,
     * renouvellements et visites (avec couleurs distinctes).
     * S6 : suivi des décisions de réunion.
     */
    public function up(): void
    {
        // ── S7 : étendre le type des événements d'agenda ──────────────────────
        Schema::table('dae_agenda_events', function (Blueprint $table) {
            if (Schema::hasColumn('dae_agenda_events', 'type')) {
                $table->string('type')->change();
            }
        });

        // ── S6 : suivi des décisions de réunion ───────────────────────────────
        Schema::table('dae_meeting_minutes', function (Blueprint $table) {
            // Référence à un événement de réunion dans l'agenda (lié)
            if (!Schema::hasColumn('dae_meeting_minutes', 'agenda_event_id')) {
                $table->unsignedBigInteger('agenda_event_id')->nullable()->after('client_id');
                $table->foreign('agenda_event_id')->references('id')->on('dae_agenda_events')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dae_meeting_minutes', function (Blueprint $table) {
            if (Schema::hasColumn('dae_meeting_minutes', 'agenda_event_id')) {
                $table->dropForeign(['agenda_event_id']);
                $table->dropColumn('agenda_event_id');
            }
        });
    }
};