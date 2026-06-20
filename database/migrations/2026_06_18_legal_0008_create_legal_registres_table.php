<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_registres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->enum('type', [
                'registre_commerce', 'registre_assemblee', 'registre_presence',
                'registre_mouvements_titres', 'registre_decisions',
                'livre_journal_legal', 'autre'
            ]);
            $table->integer('annee');
            $table->json('entrees')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->date('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'type', 'annee'], 'lreg_cli_type_annee_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_registres');
    }
};
