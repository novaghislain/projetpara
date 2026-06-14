<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tva_declarations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreignId('fiscal_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period', 7); // YYYY-MM
            $table->enum('type', ['monthly', 'quarterly', 'annual'])->default('monthly');
            // TVA collectée sur ventes
            $table->decimal('tva_collected', 15, 2)->default(0);
            // TVA déductible sur achats
            $table->decimal('tva_deductible', 15, 2)->default(0);
            // TVA à reverser = collectée - déductible
            $table->decimal('tva_net', 15, 2)->default(0);
            // Décomposition
            $table->json('details')->nullable(); // détails par taux
            $table->enum('status', ['draft', 'submitted', 'approved', 'paid'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'period', 'type']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tva_declarations');
    }
};
