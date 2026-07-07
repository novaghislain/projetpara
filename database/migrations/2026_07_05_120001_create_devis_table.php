<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->decimal('montant', 12, 0)->default(0);
            $table->string('statut')->default('brouillon'); // brouillon, envoye, relance, signe, refuse, expire
            $table->date('date_validite')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};
