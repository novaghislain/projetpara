<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_compliance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('intitule', 255);
            $table->enum('type', [
                'document_légal', 'autorisation', 'déclaration_obligatoire',
                'obligation_sociale', 'certification', 'audit', 'autre'
            ]);
            $table->string('organisme', 255)->comment('DGI, CNSS, RCCM, CRIET...');
            $table->enum('periodicite', ['unique', 'mensuel', 'trimestriel', 'semestriel', 'annuel', 'bisannuel', 'quinquennal']);
            $table->date('date_echeance');
            $table->date('date_derniere_conformite')->nullable();
            $table->enum('statut', ['conforme', 'non_conforme', 'en_cours', 'expiré', 'à_vérifier'])->default('à_vérifier');
            $table->integer('alerte_avant')->default(30);
            $table->string('document_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('responsable')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'lcomp_cli_statut_idx');
            $table->index(['client_id', 'date_echeance'], 'lcomp_cli_echeance_idx');
            $table->index(['client_id', 'type'], 'lcomp_cli_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_compliance');
    }
};
