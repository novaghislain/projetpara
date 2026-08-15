<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── rh_employees ──────────────────────────────────────────────
        if (!Schema::hasTable('rh_employees')) {
            Schema::create('rh_employees', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('client_id')->index();
                $table->string('matricule')->nullable();
                $table->string('civilite')->nullable(); // M. / Mme
                $table->string('nom');
                $table->string('prenom');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('adresse')->nullable();
                $table->date('date_naissance')->nullable();
                $table->string('lieu_naissance')->nullable();
                $table->string('nationalite')->nullable();
                $table->string('situation_matrimoniale')->nullable();
                $table->integer('nombre_enfants')->default(0);
                $table->string('poste')->nullable();
                $table->string('departement')->nullable();
                $table->date('date_embauche')->nullable();
                $table->date('date_depart')->nullable();
                $table->string('type_contrat')->nullable(); // CDI, CDD, Stage, etc.
                $table->decimal('salaire_base', 15, 2)->nullable();
                $table->string('cnss_number')->nullable();
                $table->string('ifu_number')->nullable();
                $table->string('banque')->nullable();
                $table->string('iban')->nullable();
                $table->string('urgence_nom')->nullable();
                $table->string('urgence_phone')->nullable();
                $table->string('photo')->nullable();
                $table->string('status')->default('actif'); // actif, inactif, suspendu
                $table->uuid('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── rh_contracts ──────────────────────────────────────────────
        if (!Schema::hasTable('rh_contracts')) {
            Schema::create('rh_contracts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('employee_id')->index();
                $table->string('type'); // CDI, CDD, Stage, etc.
                $table->date('date_debut');
                $table->date('date_fin')->nullable();
                $table->decimal('salaire', 15, 2)->nullable();
                $table->string('poste')->nullable();
                $table->text('notes')->nullable();
                $table->string('statut')->default('actif');
                $table->string('fichier')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── rh_leave_requests ─────────────────────────────────────────
        if (!Schema::hasTable('rh_leave_requests')) {
            Schema::create('rh_leave_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('employee_id')->index();
                $table->string('type'); // conge, maladie, maternite, paternite, formation, autre
                $table->date('date_debut');
                $table->date('date_fin');
                $table->integer('duree_jours')->nullable();
                $table->text('motif')->nullable();
                $table->string('statut')->default('pending'); // pending, approved, rejected, cancelled
                $table->uuid('approbateur_id')->nullable();
                $table->text('notes_approbateur')->nullable();
                $table->timestamp('date_approbation')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── rh_payrolls ───────────────────────────────────────────────
        if (!Schema::hasTable('rh_payrolls')) {
            Schema::create('rh_payrolls', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('employee_id')->index();
                $table->string('mois'); // ex: 2024-08
                $table->decimal('salaire_base', 15, 2)->default(0);
                $table->decimal('primes', 15, 2)->default(0);
                $table->decimal('deductions', 15, 2)->default(0);
                $table->decimal('cnss_employe', 15, 2)->default(0);
                $table->decimal('cnss_employeur', 15, 2)->default(0);
                $table->decimal('impot', 15, 2)->default(0);
                $table->decimal('net_a_payer', 15, 2)->default(0);
                $table->string('statut')->default('brouillon'); // brouillon, validé, payé
                $table->date('date_paiement')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── rh_attendance ─────────────────────────────────────────────
        if (!Schema::hasTable('rh_attendance')) {
            Schema::create('rh_attendance', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('employee_id')->index();
                $table->date('date');
                $table->time('heure_arrivee')->nullable();
                $table->time('heure_depart')->nullable();
                $table->string('statut')->default('present'); // present, absent, retard, permission
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── rh_expenses ───────────────────────────────────────────────
        if (!Schema::hasTable('rh_expenses')) {
            Schema::create('rh_expenses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('employee_id')->index();
                $table->string('type'); // transport, restauration, hebergement, autre
                $table->decimal('montant', 15, 2);
                $table->date('date');
                $table->text('description')->nullable();
                $table->string('justificatif')->nullable();
                $table->string('statut')->default('pending'); // pending, approved, rejected, paid
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── rh_trainings ──────────────────────────────────────────────
        if (!Schema::hasTable('rh_trainings')) {
            Schema::create('rh_trainings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('employee_id')->index();
                $table->string('titre');
                $table->string('organisme')->nullable();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->decimal('cout', 15, 2)->nullable();
                $table->string('statut')->default('planifie'); // planifie, en_cours, termine, annule
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ─── rh_alerts ─────────────────────────────────────────────────
        if (!Schema::hasTable('rh_alerts')) {
            Schema::create('rh_alerts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('client_id')->index();
                $table->uuid('employee_id')->nullable();
                $table->string('type'); // contrat_expire, anniversaire_embauche, visite_medicale, etc.
                $table->string('titre');
                $table->text('message')->nullable();
                $table->date('date_echeance')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_alerts');
        Schema::dropIfExists('rh_trainings');
        Schema::dropIfExists('rh_expenses');
        Schema::dropIfExists('rh_attendance');
        Schema::dropIfExists('rh_payrolls');
        Schema::dropIfExists('rh_leave_requests');
        Schema::dropIfExists('rh_contracts');
        Schema::dropIfExists('rh_employees');
    }
};
