<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_acts_library', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable()->comment('NULL = modèle global GEL');
            $table->string('titre', 255);
            $table->string('categorie', 100)->comment('constitution, AG, contrat, PV, statuts, autre');
            $table->string('type_societe', 50)->nullable()->comment('SARL, SA, SAS, toutes');
            $table->longText('contenu')->comment('template avec variables {{nom}}');
            $table->json('variables')->comment('liste des variables avec description');
            $table->string('droit_applicable', 100)->default('OHADA / Droit béninois');
            $table->integer('version')->default(1);
            $table->boolean('is_public')->default(false)->comment('accessible à tous les tenants');
            $table->boolean('is_validated')->default(false)->comment('validé par juriste GEL');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('tags')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'categorie'], 'lacts_cli_cat_idx');
            $table->index(['is_public', 'is_validated'], 'lacts_pub_val_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_acts_library');
    }
};
