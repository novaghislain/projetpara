<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_morgue_depots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_dossier')->unique();
            $table->string('defunt_nom');
            $table->string('defunt_prenom')->nullable();
            $table->date('date_deces')->nullable();
            $table->date('date_depot');
            $table->date('date_sortie')->nullable();
            $table->string('famille_contact')->nullable();
            $table->string('famille_nom')->nullable();
            $table->string('type_conservation')->default('normale'); // normale, refrigeree, embaumement
            $table->integer('nb_jours')->default(0);
            $table->decimal('tarif_journalier', 12, 2)->default(0);
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('en_cours'); // en_cours, sorti, facture, annule
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'morgue_dep_cli_stat');
            $table->index(['client_id', 'date_depot'], 'morgue_dep_cli_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_morgue_depots');
    }
};
