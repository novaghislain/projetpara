<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_location_biens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('reference_bien')->nullable();
            $table->string('designation');
            $table->string('type')->default('appartement'); // appartement, maison, local, terrain, bureau
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('quartier')->nullable();
            $table->decimal('surface', 10, 2)->nullable();
            $table->integer('nb_pieces')->nullable();
            $table->decimal('loyer_mensuel', 12, 2)->default(0);
            $table->decimal('charges_mensuelles', 12, 2)->default(0);
            $table->decimal('caution', 12, 2)->default(0);
            $table->string('statut')->default('disponible'); // disponible, loue, en_travaux, reserve
            $table->string('locataire_actuel')->nullable();
            $table->date('date_debut_bail')->nullable();
            $table->date('date_fin_bail')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut'], 'loc_biens_cli_stat');
            $table->index(['client_id', 'ville'], 'loc_biens_cli_ville');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_location_biens');
    }
};
