<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_contract_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('legal_contracts')->cascadeOnDelete();
            $table->string('signataire_nom', 255);
            $table->string('signataire_email', 255);
            $table->string('signataire_role', 100);
            $table->enum('statut', ['en_attente', 'signé', 'refusé'])->default('en_attente');
            $table->timestamp('date_signature')->nullable();
            $table->string('signature_path', 500)->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'statut'], 'lcs_ctr_statut_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_contract_signatures');
    }
};
