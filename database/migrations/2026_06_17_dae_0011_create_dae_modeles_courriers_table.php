<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_modeles_courriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nom');
            $table->string('type')->nullable();
            $table->string('objet_defaut')->nullable();
            $table->longText('corps')->nullable();
            $table->json('variables')->nullable();
            $table->string('categorie')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['categorie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_modeles_courriers');
    }
};
