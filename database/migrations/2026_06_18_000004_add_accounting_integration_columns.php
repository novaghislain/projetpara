<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $journalLineCols = ['reference_document', 'reference_type', 'lettrage_code', 'lettrage_at', 'due_date', 'cost_center_id'];

    public function up(): void
    {
        // ── 1. Créer les tables manquantes ──────────────────
        if (!Schema::hasTable('accounting_cost_centers')) {
            Schema::create('accounting_cost_centers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id');
                $table->string('code', 20);
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['client_id', 'code']);
                $table->index('client_id');
            });
        }

        if (!Schema::hasTable('accounting_lettrages')) {
            Schema::create('accounting_lettrages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id');
                $table->string('code', 50);
                $table->foreignId('account_id')->constrained('accounting_accounts')->cascadeOnDelete();
                $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
                $table->decimal('montant', 18, 2)->default(0);
                $table->date('date_lettrage');
                $table->enum('status', ['ouvert', 'partial', 'lettre', 'deprecie'])->default('ouvert');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['client_id', 'account_id', 'status'])->name('lettr_cli_acc_status_idx');
            });
        }

        // ── 2. Ajouter colonnes manquantes sur accounting_journal_lines ────
        Schema::table('accounting_journal_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_journal_lines', 'reference_document')) {
                $table->string('reference_document')->nullable()->after('label');
            }
            if (!Schema::hasColumn('accounting_journal_lines', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('reference_document');
            }
            if (!Schema::hasColumn('accounting_journal_lines', 'lettrage_code')) {
                $table->string('lettrage_code', 50)->nullable()->after('credit');
            }
            if (!Schema::hasColumn('accounting_journal_lines', 'lettrage_at')) {
                $table->timestamp('lettrage_at')->nullable()->after('lettrage_code');
            }
            if (!Schema::hasColumn('accounting_journal_lines', 'due_date')) {
                $table->date('due_date')->nullable()->after('lettrage_at');
            }
            if (!Schema::hasColumn('accounting_journal_lines', 'cost_center_id')) {
                $table->foreignId('cost_center_id')->nullable()->constrained('accounting_cost_centers')->nullOnDelete()->after('due_date');
            }
        });

        // ── 3. Ajouter morphs aux écritures comptables ──────
        Schema::table('accounting_journals', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_journals', 'sourceable_type')) {
                $table->nullableMorphs('sourceable');
            }
        });

        // ── 4. Ajouter colonnes aux réconciliations bancaires ──
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_reconciliations', 'reference')) {
                $table->string('reference', 50)->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('bank_reconciliations', 'solde_comptable')) {
                $table->decimal('solde_comptable', 18, 2)->default(0)->after('balance_per_books');
            }
            if (!Schema::hasColumn('bank_reconciliations', 'ecart')) {
                $table->decimal('ecart', 18, 2)->default(0)->after('solde_comptable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_journals', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_journals', 'sourceable_type')) {
                $table->dropMorphs('sourceable');
            }
        });
        Schema::table('accounting_journal_lines', function (Blueprint $table) {
            foreach (array_reverse($this->journalLineCols) as $col) {
                if (Schema::hasColumn('accounting_journal_lines', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('accounting_lettrages');
        Schema::dropIfExists('accounting_cost_centers');
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            foreach (['ecart', 'solde_comptable', 'reference'] as $col) {
                if (Schema::hasColumn('bank_reconciliations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
