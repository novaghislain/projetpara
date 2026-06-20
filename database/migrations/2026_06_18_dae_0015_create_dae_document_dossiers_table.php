<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_document_dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->foreignId('parent_id')->nullable()->constrained('dae_document_dossiers')->cascadeOnDelete();
            $table->string('couleur', 20)->nullable();
            $table->text('description')->nullable();
            $table->integer('ordre')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_document_dossiers');
    }
};
