<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('rh_employees')->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->enum('type', ['CDI', 'CDD', 'INTERIM', 'STAGE', 'PRESTATION'])->default('CDI');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->integer('duree_mois')->nullable();
            $table->string('poste')->nullable();
            $table->string('departement')->nullable();
            $table->decimal('salaire', 15, 2)->nullable();
            $table->integer('periode_essai_jours')->default(0);
            $table->boolean('renouvelable')->default(false);
            $table->date('date_fin_periode_essai')->nullable();
            $table->enum('statut', ['brouillon', 'actif', 'expire', 'resilie', 'renouvele'])->default('brouillon');
            $table->string('fichier_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'statut']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_contracts');
    }
};
