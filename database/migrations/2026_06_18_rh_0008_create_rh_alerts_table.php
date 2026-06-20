<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('rh_employees')->nullOnDelete();
            $table->enum('type', [
                'contrat_fin', 'cnss', 'visite_medicale', 'conge',
                'anniversaire', 'document', 'periode_essai', 'autre'
            ])->default('autre');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->date('date_echeance');
            $table->dateTime('date_declenchement')->nullable();
            $table->enum('statut', ['active', 'desactivee', 'resolue', 'ignoree'])->default('active');
            $table->integer('days_before')->default(7); // nb jours avant échéance pour déclencher
            $table->timestamps();

            $table->index(['client_id', 'statut']);
            $table->index('date_echeance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_alerts');
    }
};
