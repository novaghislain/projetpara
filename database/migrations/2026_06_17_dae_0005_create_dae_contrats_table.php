<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->string('titre');
            $table->string('type_contrat')->nullable();
            $table->string('partie_adverse')->nullable();
            $table->date('date_signature')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->integer('duree_mois')->nullable();
            $table->decimal('montant', 15, 2)->nullable();
            $table->string('devise', 3)->default('XOF');
            $table->text('objet')->nullable();
            $table->text('conditions')->nullable();
            $table->enum('statut', ['brouillon', 'actif', 'expire', 'resilie', 'renouvele'])->default('brouillon');
            $table->string('fichier')->nullable();
            $table->date('date_preavis')->nullable();
            $table->date('date_renouvellement')->nullable();
            $table->boolean('renouvelable')->default(false);
            $table->date('renouvele_le')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'date_fin']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_contrats');
    }
};
