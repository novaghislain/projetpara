<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_tax_declarations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->enum('tax_type', ['tva', 'is', 'its', 'cnss', 'vps', 'aib']);
            $table->string('reference', 50);
            $table->enum('period_type', ['mensuel', 'trimestriel', 'annuel'])->default('mensuel');
            $table->integer('period_month')->nullable();
            $table->integer('period_quarter')->nullable();
            $table->integer('period_year');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->date('date_echeance');
            $table->date('date_depot')->nullable();

            // Montants calculés
            $table->decimal('base_imposable', 18, 2)->default(0);
            $table->decimal('taux', 5, 2)->default(0);
            $table->decimal('montant_dut', 18, 2)->default(0);
            $table->decimal('montant_paye', 18, 2)->default(0);
            $table->decimal('penalites', 18, 2)->default(0);
            $table->decimal('solde', 18, 2)->default(0);

            // TVA spécificités
            $table->decimal('tva_collectee', 18, 2)->default(0)->nullable();
            $table->decimal('tva_recuperable', 18, 2)->default(0)->nullable();
            $table->decimal('tva_net', 18, 2)->default(0)->nullable();
            $table->decimal('credit_tva', 18, 2)->default(0)->nullable();

            // IS spécificités
            $table->decimal('resultat_fiscal', 18, 2)->default(0)->nullable();
            $table->decimal('acomptes_verses', 18, 2)->default(0)->nullable();

            // ITS spécificités
            $table->json('tranches')->nullable();

            // CNSS spécificités
            $table->decimal('part_employeur', 18, 2)->default(0)->nullable();
            $table->decimal('part_salarie', 18, 2)->default(0)->nullable();

            // Statut
            $table->enum('status', ['brouillon', 'calcule', 'depose', 'paye', 'en_retard'])->default('brouillon');
            $table->text('notes')->nullable();

            // Validation
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('accounting_journals')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'tax_type', 'reference'])->name('td_cli_tax_ref_idx');
            $table->index(['client_id', 'fiscal_year_id', 'tax_type'])->name('td_cli_fy_tax_idx');
            $table->index(['client_id', 'status'])->name('td_cli_status_idx');
            $table->index(['client_id', 'date_echeance'])->name('td_cli_echeance_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_tax_declarations');
    }
};
