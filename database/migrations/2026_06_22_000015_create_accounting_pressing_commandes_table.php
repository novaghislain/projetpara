<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_pressing_commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero_commande')->unique();
            $table->string('client_nom');
            $table->string('client_contact')->nullable();
            $table->date('date_depot');
            $table->date('date_retrait_prevu')->nullable();
            $table->date('date_retrait')->nullable();
            $table->integer('nb_articles')->default(0);
            $table->text('articles')->nullable(); // description des vetements
            $table->string('type_service')->default('nettoyage'); // nettoyage, repassage, teinturerie, couture
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('acompte', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->string('statut')->default('en_cours'); // en_cours, pret, remis, annule
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'pressing_cde_cli_stat');
            $table->index(['client_id', 'date_depot'], 'pressing_cde_cli_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_pressing_commandes');
    }
};
