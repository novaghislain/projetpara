<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('matricule')->nullable();
            $table->string('civilite')->nullable(); // M, Mme, Mlle
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('adresse')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('nationalite')->nullable();
            $table->string('situation_matrimoniale')->nullable(); // celibataire, marie, divorce, veuf
            $table->integer('nombre_enfants')->default(0);
            $table->string('poste')->nullable();
            $table->string('departement')->nullable();
            $table->date('date_embauche')->nullable();
            $table->date('date_depart')->nullable();
            $table->enum('type_contrat', ['CDI', 'CDD', 'INTERIM', 'STAGE', 'PRESTATION'])->default('CDI');
            $table->decimal('salaire_base', 15, 2)->nullable();
            $table->string('cnss_number')->nullable();
            $table->string('ifu_number')->nullable();
            $table->string('banque')->nullable();
            $table->string('iban')->nullable();
            $table->string('urgence_nom')->nullable();
            $table->string('urgence_phone')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['actif', 'suspendu', 'quitte'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index('matricule');
            $table->index('departement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_employees');
    }
};
