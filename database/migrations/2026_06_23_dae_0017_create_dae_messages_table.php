<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dae_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['appel', 'message', 'note', 'info'])->default('message');
            $table->string('expediteur_name')->nullable();
            $table->string('expediteur_entreprise')->nullable();
            $table->string('expediteur_contact')->nullable();
            $table->foreignId('destinataire_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('objet');
            $table->text('contenu')->nullable();
            $table->enum('urgence', ['normal', 'urgent'])->default('normal');
            $table->enum('statut', ['recu', 'lu', 'traite', 'archive'])->default('recu');
            $table->dateTime('lu_at')->nullable();
            $table->dateTime('traite_at')->nullable();
            $table->boolean('appel_rappele')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['client_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dae_messages');
    }
};
