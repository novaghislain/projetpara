<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->string('nom_entreprise', 200);
            $table->string('email', 255)->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('ifu', 50)->nullable();
            $table->string('rc', 50)->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville', 100)->nullable();
            $table->string('pays', 100)->default('Bénin');
            $table->string('statut', 20)->default('actif'); // actif, inactif, en_attente
            $table->string('code_client', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_clients');
    }
};
