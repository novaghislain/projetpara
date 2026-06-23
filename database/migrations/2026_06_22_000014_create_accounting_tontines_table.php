<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_tontines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('nom_groupe');
            $table->string('description')->nullable();
            $table->integer('nb_membres')->default(0);
            $table->decimal('montant_cotisation', 12, 2)->default(0);
            $table->string('frequence')->default('mensuelle'); // hebdomadaire, mensuelle, trimestrielle
            $table->decimal('montant_caisse', 12, 2)->default(0);
            $table->date('date_creation')->nullable();
            $table->string('statut')->default('active'); // active, suspendue, terminee
            $table->text('regles')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'nom_groupe']);
            $table->index(['client_id', 'statut'], 'tontines_cli_stat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_tontines');
    }
};
