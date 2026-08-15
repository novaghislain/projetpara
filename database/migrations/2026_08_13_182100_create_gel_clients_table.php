<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('gel_clients')) {
            Schema::create('gel_clients', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('independant_client_id')->nullable()->index();
                $table->string('nom_entreprise');
                $table->string('sigle')->nullable();
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->text('adresse')->nullable();
                $table->string('ville')->nullable();
                $table->string('ifu')->nullable();
                $table->string('rc')->nullable();
                $table->string('secteur')->nullable();
                $table->string('logo')->nullable();
                $table->string('statut')->default('actif');
                $table->unsignedBigInteger('compte_comptable_id')->nullable()->index();
                $table->integer('score_conformite')->default(0);
                $table->timestamp('score_calcule_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gel_clients');
    }
};
