<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_ecritures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('gel_cabinets')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('gel_clients')->nullOnDelete();
            $table->foreignId('journal_id')->constrained('gel_journaux')->cascadeOnDelete();
            $table->foreignId('exercice_id')->nullable()->constrained('gel_exercices')->nullOnDelete();
            $table->string('numero', 50)->nullable();
            $table->date('date_ecriture');
            $table->date('date_piece')->nullable();
            $table->string('ref_piece', 100)->nullable();
            $table->string('libelle', 500);
            $table->decimal('total_debit', 15, 0)->default(0);
            $table->decimal('total_credit', 15, 0)->default(0);
            $table->boolean('valide')->default(false);
            $table->timestamp('valide_at')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('createur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cabinet_id', 'client_id', 'date_ecriture']);
            $table->index(['cabinet_id', 'journal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_ecritures');
    }
};
