<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_assemblies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->enum('type', ['AGO','AGE','AGOE','CA','Bureau','Autre']);
            $table->integer('annee');
            $table->date('date_convocation')->nullable();
            $table->date('date_tenue');
            $table->string('lieu', 255);
            $table->decimal('quorum_requis', 5, 2)->nullable()->comment('% de présence requis');
            $table->decimal('quorum_atteint', 5, 2)->nullable();
            $table->json('ordre_du_jour');
            $table->json('resolutions')->nullable();
            $table->json('participants')->nullable();
            $table->enum('statut', ['planifiée','tenue','annulée','reportée'])->default('planifiée');
            $table->string('pv_path', 500)->nullable();
            $table->boolean('pv_approuve')->default(false);
            $table->boolean('convocation_envoyee')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['client_id', 'annee', 'type'], 'la_cli_annee_type_idx');
            $table->index(['client_id', 'statut'], 'la_cli_statut_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_assemblies');
    }
};
