<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_veille', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('titre', 255);
            $table->text('description')->nullable();
            $table->string('source', 255)->nullable()->comment('site web, journal officiel, newsletter');
            $table->string('categorie', 100)->nullable()->comment('fiscal, social, commercial, OHADA, autre');
            $table->date('date_publication')->nullable();
            $table->string('url', 500)->nullable();
            $table->text('impact')->nullable()->comment('impact potentiel sur l\'entreprise');
            $table->json('tags')->nullable();
            $table->boolean('est_lu')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['client_id', 'categorie'], 'lv_cli_cat_idx');
            $table->index(['client_id', 'date_publication'], 'lv_cli_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_veille');
    }
};
