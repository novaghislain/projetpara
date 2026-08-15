<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Tables comptables SYSCOHADA pour GEL Cabinet
 *
 * Crée les tables nécessaires au module de comptabilité :
 *   - gel_comptes_comptables   : Plan comptable (SYSCOHADA)
 *   - gel_journaux             : Journaux comptables
 *   - gel_exercices            : Exercices fiscaux
 *   - gel_ecritures            : Écritures comptables (en-têtes)
 *   - gel_lignes_ecriture      : Lignes d'écritures (débit/crédit)
 *   - gel_declarations_fiscales: Déclarations TVA & fiscales
 *   - gel_fixed_assets         : Immobilisations
 *   - gel_revenue_recognition  : Reconnaissance de revenus
 *   - gel_tva_declarations     : Déclarations TVA
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── Plan Comptable SYSCOHADA ──────────────────────────────────────
        if (!Schema::hasTable('gel_comptes_comptables')) {
            Schema::create('gel_comptes_comptables', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('code', 20)->index();
                $table->string('intitule');
                $table->enum('classe', ['1','2','3','4','5','6','7','8'])->nullable();
                $table->integer('niveau')->default(1); // 1=classe, 2=compte, 3=sous-compte
                $table->enum('type_compte', ['actif', 'passif', 'charge', 'produit', 'bilan'])->nullable();
                $table->boolean('actif')->default(true);
                $table->boolean('est_syscohada')->default(false); // plan standard
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['cabinet_id', 'client_id', 'code']);
            });
        }

        // ─── Plan Comptable Standard SYSCOHADA (référentiel) ──────────────
        if (!Schema::hasTable('plan_comptable_syscohada')) {
            Schema::create('plan_comptable_syscohada', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique()->index();
                $table->string('intitule');
                $table->enum('classe', ['1','2','3','4','5','6','7','8'])->nullable();
                $table->integer('niveau')->default(1);
                $table->enum('type_compte', ['actif', 'passif', 'charge', 'produit', 'bilan'])->nullable();
                $table->timestamps();
            });
        }

        // ─── Journaux Comptables ───────────────────────────────────────────
        if (!Schema::hasTable('gel_journaux')) {
            Schema::create('gel_journaux', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('code', 10);
                $table->string('libelle');
                $table->enum('type', ['ventes', 'achats', 'banque', 'caisse', 'operations_diverses', 'a_nouveaux'])->default('operations_diverses');
                $table->string('compte_contrepartie')->nullable(); // compte de banque/caisse associé
                $table->boolean('actif')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── Exercices Comptables ─────────────────────────────────────────
        if (!Schema::hasTable('gel_exercices')) {
            Schema::create('gel_exercices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->integer('annee');
                $table->date('date_debut');
                $table->date('date_fin');
                $table->enum('statut', ['ouvert', 'clos', 'verrouille'])->default('ouvert');
                $table->timestamp('clos_at')->nullable();
                $table->unsignedBigInteger('clos_par')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── Écritures Comptables (en-têtes) ──────────────────────────────
        if (!Schema::hasTable('gel_ecritures')) {
            Schema::create('gel_ecritures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->unsignedBigInteger('journal_id')->nullable()->index();
                $table->unsignedBigInteger('exercice_id')->nullable()->index();
                $table->date('date_ecriture');
                $table->string('numero_piece')->nullable(); // N° de pièce justificative
                $table->string('libelle');
                $table->boolean('valide')->default(false); // false = brouillon
                $table->timestamp('valide_at')->nullable();
                $table->unsignedBigInteger('valide_par')->nullable();
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                // Source (facture, dépense, etc.)
                $table->string('source_type')->nullable(); // 'invoice', 'expense', etc.
                $table->unsignedBigInteger('source_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['cabinet_id', 'client_id', 'date_ecriture']);
            });
        }

        // ─── Lignes d'Écritures (débit / crédit) ──────────────────────────
        if (!Schema::hasTable('gel_lignes_ecriture')) {
            Schema::create('gel_lignes_ecriture', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ecriture_id')->index();
                $table->unsignedBigInteger('compte_id')->index(); // FK vers gel_comptes_comptables
                $table->unsignedBigInteger('tiers_id')->nullable()->index(); // FK vers clients (tiers)
                $table->enum('sens', ['debit', 'credit']);
                $table->decimal('montant', 15, 2);
                $table->string('libelle_ligne')->nullable();
                $table->string('reference')->nullable();
                $table->date('date_echeance')->nullable(); // pour les tiers
                $table->boolean('lettree')->default(false); // lettrage
                $table->string('lettre')->nullable(); // lettre de lettrage
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('ecriture_id')->references('id')->on('gel_ecritures')->onDelete('cascade');
            });
        }

        // ─── Déclarations Fiscales ─────────────────────────────────────────
        if (!Schema::hasTable('gel_declarations_fiscales')) {
            Schema::create('gel_declarations_fiscales', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->enum('type', ['tva', 'is', 'irs', 'autre'])->default('tva');
                $table->string('periode'); // ex: 2026-07
                $table->decimal('base_imposable', 15, 2)->default(0);
                $table->decimal('montant_impot', 15, 2)->default(0);
                $table->decimal('montant_paye', 15, 2)->default(0);
                $table->enum('statut', ['brouillon', 'soumise', 'validee', 'payee'])->default('brouillon');
                $table->date('date_soumission')->nullable();
                $table->date('date_echeance')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── Immobilisations ───────────────────────────────────────────────
        if (!Schema::hasTable('gel_fixed_assets')) {
            Schema::create('gel_fixed_assets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('designation');
                $table->string('categorie')->nullable(); // matériel, bâtiment, etc.
                $table->date('date_acquisition');
                $table->decimal('valeur_acquisition', 15, 2);
                $table->decimal('valeur_residuelle', 15, 2)->default(0);
                $table->integer('duree_amortissement')->default(5); // en années
                $table->enum('methode_amortissement', ['lineaire', 'degressif'])->default('lineaire');
                $table->decimal('cumul_amortissements', 15, 2)->default(0);
                $table->decimal('valeur_nette_comptable', 15, 2)->nullable();
                $table->enum('statut', ['actif', 'cede', 'mis_au_rebut'])->default('actif');
                $table->string('numero_serie')->nullable();
                $table->string('fournisseur')->nullable();
                $table->unsignedBigInteger('compte_id')->nullable(); // compte comptable
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── Reconnaissance de Revenus ─────────────────────────────────────
        if (!Schema::hasTable('gel_revenue_recognition')) {
            Schema::create('gel_revenue_recognition', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('libelle');
                $table->decimal('montant_total', 15, 2);
                $table->date('date_debut');
                $table->date('date_fin');
                $table->enum('statut', ['en_cours', 'termine', 'annule'])->default('en_cours');
                $table->decimal('montant_reconnu', 15, 2)->default(0);
                $table->unsignedBigInteger('source_id')->nullable(); // FK facture
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_revenue_recognition');
        Schema::dropIfExists('gel_fixed_assets');
        Schema::dropIfExists('gel_declarations_fiscales');
        Schema::dropIfExists('gel_lignes_ecriture');
        Schema::dropIfExists('gel_ecritures');
        Schema::dropIfExists('gel_exercices');
        Schema::dropIfExists('gel_journaux');
        Schema::dropIfExists('plan_comptable_syscohada');
        Schema::dropIfExists('gel_comptes_comptables');
    }
};
