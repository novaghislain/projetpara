<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S1 — Rapports de restructuration (avant/après).
     * `--dry-run` écrit status=proposed ; `--apply` uniquement après validation
     * explicite (status=approved) puis passe à executed. Aucune suppression.
     */
    public function up(): void
    {
        Schema::create('restructure_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('status', 20)->default('proposed'); // proposed|approved|executed|rejected
            $table->longText('payload')->nullable();           // JSON {before, mapping, doublons, orphelins, after}
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restructure_reports');
    }
};