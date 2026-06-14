<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL : ajouter les nouvelles valeurs à l'enum existant
        $types = ['operations_diverses', 'salaire', 'investissement', 'anouveaux', 'paie', 'hav'];
        foreach ($types as $t) {
            DB::statement("ALTER TABLE accounting_journals DROP CONSTRAINT IF EXISTS accounting_journals_journal_type_check");
        }

        // Remplacer la contrainte check par une nouvelle avec tous les types SYSCOHADA
        DB::statement("ALTER TABLE accounting_journals DROP CONSTRAINT IF EXISTS accounting_journals_journal_type_check");
        DB::statement("ALTER TABLE accounting_journals ADD CONSTRAINT accounting_journals_journal_type_check
            CHECK (journal_type IN ('achat','vente','banque','caisse','od','operations_diverses','salaire','investissement','anouveaux','paie','hav'))");

        Schema::table('accounting_journals', function (Blueprint $table) {
            // Exercice comptable
            $table->foreignId('fiscal_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete()->after('client_id');
            // Numéro de pièce séquentiel (généré automatiquement)
            $table->string('numero_piece', 50)->nullable()->after('reference');
            // Validation
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->timestamp('validated_at')->nullable()->after('validated_by');
            // Extourne
            $table->boolean('is_reversal')->default(false)->after('status');
            $table->foreignId('reversed_journal_id')->nullable()->constrained('accounting_journals')->nullOnDelete()->after('is_reversal');
            // Source (automatique depuis un autre module)
            $table->string('source_module', 50)->nullable()->after('description');

            $table->index('numero_piece');
            $table->index('fiscal_year_id');
        });
    }

    public function down(): void
    {
        Schema::table('accounting_journals', function (Blueprint $table) {
            $table->dropColumn([
                'fiscal_year_id', 'numero_piece', 'validated_by', 'validated_at',
                'is_reversal', 'reversed_journal_id', 'source_module',
            ]);
        });
    }
};
