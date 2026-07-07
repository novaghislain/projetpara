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
        Schema::create('ecritures', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->unsignedBigInteger('exercice_id')->nullable();
            $table->date('date_ecriture');
            $table->string('numero_piece')->nullable(); // Pièce justificative
            $table->string('reference')->unique(); // Ex: VTE-2026-00042
            $table->string('libelle');
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->boolean('is_validee')->default(false); // Validée = plus modifiable
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->boolean('is_extourne')->default(false); // Si c'est une extourne
            $table->foreignId('extourne_id')->nullable()->constrained('ecritures')->nullOnDelete(); // L'écriture d'origine annulée
            $table->string('piece_jointe_path')->nullable(); // Chemin scan
            $table->boolean('is_recurrente')->default(false); // Issue d'un modèle récurrent ?
            $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecritures');
    }
};
