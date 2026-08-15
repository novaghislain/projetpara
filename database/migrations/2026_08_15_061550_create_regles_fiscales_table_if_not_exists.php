<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('regles_fiscales')) {
            Schema::create('regles_fiscales', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code_pays');
                $table->string('type_impot');
                $table->decimal('taux', 15, 4);
                $table->json('conditions')->nullable();
                $table->date('date_debut_validite');
                $table->date('date_fin_validite')->nullable();
                $table->string('source_reglementaire')->nullable();
                $table->integer('version')->default(1);
                $table->string('statut')->default('active'); // active, inactive
                $table->unsignedBigInteger('cree_par')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('regles_fiscales');
    }
};
