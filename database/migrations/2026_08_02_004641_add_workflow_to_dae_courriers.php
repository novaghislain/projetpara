<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dae_courriers', function (Blueprint $table) {
            // Remplace le statut simple par un workflow en 6 étapes
            if (!Schema::hasColumn('dae_courriers', 'workflow_step')) {
                // creation > visa > validation > signature > envoi > archive
                $table->string('workflow_step')->default('creation')->after('statut');
            }
            if (!Schema::hasColumn('dae_courriers', 'visa_par')) {
                $table->unsignedBigInteger('visa_par')->nullable()->after('traite_par');
            }
            if (!Schema::hasColumn('dae_courriers', 'visa_at')) {
                $table->timestamp('visa_at')->nullable()->after('visa_par');
            }
            if (!Schema::hasColumn('dae_courriers', 'signe_par')) {
                $table->unsignedBigInteger('signe_par')->nullable()->after('visa_at');
            }
            if (!Schema::hasColumn('dae_courriers', 'signe_at')) {
                $table->timestamp('signe_at')->nullable()->after('signe_par');
            }
            if (!Schema::hasColumn('dae_courriers', 'envoye_at')) {
                $table->timestamp('envoye_at')->nullable()->after('signe_at');
            }
            if (!Schema::hasColumn('dae_courriers', 'archive_at')) {
                $table->timestamp('archive_at')->nullable()->after('envoye_at');
            }
            if (!Schema::hasColumn('dae_courriers', 'workflow_notes')) {
                $table->text('workflow_notes')->nullable()->after('archive_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dae_courriers', function (Blueprint $table) {
            $table->dropColumn(['workflow_step', 'visa_par', 'visa_at', 'signe_par', 'signe_at', 'envoye_at', 'archive_at', 'workflow_notes']);
        });
    }
};
