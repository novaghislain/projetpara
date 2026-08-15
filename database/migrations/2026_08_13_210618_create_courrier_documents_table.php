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
        Schema::create('courrier_documents', function (Blueprint $table) {
            $table->uuid('courrier_id');
            $table->unsignedBigInteger('document_id'); // L'ID des documents existants n'est peut-être pas UUID, à vérifier
            
            // Les Foreign keys seront ajoutées si nécessaires, on se contente du lien
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courrier_documents');
    }
};
