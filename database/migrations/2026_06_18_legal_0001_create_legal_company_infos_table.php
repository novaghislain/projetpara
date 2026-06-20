<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_company_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('raison_sociale');
            $table->enum('forme_juridique', ['SARL','SA','SAS','SASU','SNC','GIE','EI','EURL','Association','Autre']);
            $table->decimal('capital_social', 18, 2)->nullable();
            $table->date('date_creation')->nullable();
            $table->string('numero_rccm', 100)->nullable();
            $table->string('ifu', 50)->nullable();
            $table->text('siege_social')->nullable();
            $table->text('objet_social')->nullable();
            $table->integer('duree_vie')->nullable()->comment('en années');
            $table->string('exercice_social', 20)->nullable();

            // Dirigeants
            $table->string('gerant_nom', 255)->nullable();
            $table->string('gerant_prenom', 255)->nullable();
            $table->string('gerant_nationalite', 100)->nullable();
            $table->json('conseil_administration')->nullable();

            // Associés
            $table->json('associes')->nullable();

            // Statuts
            $table->string('statuts_path', 500)->nullable();
            $table->date('statuts_date')->nullable();
            $table->integer('statuts_version')->default(1);

            $table->timestamps();

            $table->index(['client_id'], 'lci_cli_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_company_infos');
    }
};
