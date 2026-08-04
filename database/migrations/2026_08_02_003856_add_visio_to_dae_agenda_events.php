<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dae_agenda_events', function (Blueprint $table) {
            if (!Schema::hasColumn('dae_agenda_events', 'visio_type')) {
                $table->string('visio_type')->nullable()->after('location'); // zoom, teams, meet, autre
            }
            if (!Schema::hasColumn('dae_agenda_events', 'visio_link')) {
                $table->string('visio_link')->nullable()->after('visio_type');
            }
            if (!Schema::hasColumn('dae_agenda_events', 'invitation_sent')) {
                $table->boolean('invitation_sent')->default(false)->after('visio_link');
            }
            if (!Schema::hasColumn('dae_agenda_events', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('invitation_sent');
            }
            if (!Schema::hasColumn('dae_agenda_events', 'guest_name')) {
                $table->string('guest_name')->nullable()->after('guest_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dae_agenda_events', function (Blueprint $table) {
            $table->dropColumn(['visio_type', 'visio_link', 'invitation_sent', 'guest_email', 'guest_name']);
        });
    }
};
