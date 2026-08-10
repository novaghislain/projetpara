<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S2 — Journal d'exécution du job `folders:calendar`.
     * Traçabilité des basculements de mois/année ("mois créé", "ok", "failure").
     */
    public function up(): void
    {
        Schema::create('folder_rollover_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('scope_label', 255)->nullable(); // "client:1" | "user:12"
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->string('status', 20)->default('ok');       // ok | created | failure
            $table->text('note')->nullable();
            $table->string('run_id', 40)->nullable()->index(); // identifiant unique d'une passe du job
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_rollover_logs');
    }
};