<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── DAE (Secrétariat numérique) ───────────────────────────────────────

        if (!Schema::hasTable('dae_agenda_events')) {
            Schema::create('dae_agenda_events', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->string('type')->nullable();
                $table->string('lieu')->nullable();
                $table->boolean('all_day')->default(false);
                $table->string('statut')->default('planifie');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_courriers')) {
            Schema::create('dae_courriers', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('objet')->nullable();
                $table->string('type')->default('entrant'); // entrant | sortant
                $table->string('expediteur')->nullable();
                $table->string('destinataire')->nullable();
                $table->text('contenu')->nullable();
                $table->string('statut')->default('recu');
                $table->string('fichier_path')->nullable();
                $table->date('date_courrier')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_contrats')) {
            Schema::create('dae_contrats', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('intitule')->nullable();
                $table->string('type')->nullable();
                $table->string('partie_contractante')->nullable();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->date('date_echeance')->nullable();
                $table->string('statut')->default('actif');
                $table->string('fichier_path')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_documents')) {
            Schema::create('dae_documents', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('nom');
                $table->string('type')->nullable();
                $table->string('categorie')->nullable();
                $table->string('fichier_path')->nullable();
                $table->bigInteger('taille')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_document_dossiers')) {
            Schema::create('dae_document_dossiers', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->text('description')->nullable();
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_taches')) {
            Schema::create('dae_taches', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('assigned_to')->nullable()->index();
                $table->uuid('created_by')->nullable()->index();
                $table->string('titre');
                $table->text('description')->nullable();
                $table->string('statut')->default('a_faire');
                $table->string('priorite')->default('normale');
                $table->date('echeance')->nullable();
                $table->timestamp('termine_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_meeting_minutes')) {
            Schema::create('dae_meeting_minutes', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('created_by')->nullable()->index();
                $table->string('titre')->nullable();
                $table->date('date_reunion')->nullable();
                $table->string('lieu')->nullable();
                $table->text('ordre_du_jour')->nullable();
                $table->text('compte_rendu')->nullable();
                $table->json('participants')->nullable();
                $table->string('statut')->default('brouillon');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_messages')) {
            Schema::create('dae_messages', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('sender_id')->nullable()->index();
                $table->string('sender_type')->nullable();
                $table->text('contenu')->nullable();
                $table->boolean('est_lu')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_emails')) {
            Schema::create('dae_emails', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('from_email')->nullable();
                $table->string('to_email')->nullable();
                $table->string('subject')->nullable();
                $table->text('body')->nullable();
                $table->string('direction')->default('entrant');
                $table->boolean('est_lu')->default(false);
                $table->timestamp('received_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_rapports')) {
            Schema::create('dae_rapports', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('created_by')->nullable()->index();
                $table->string('titre');
                $table->string('type')->nullable();
                $table->text('contenu')->nullable();
                $table->string('fichier_path')->nullable();
                $table->string('statut')->default('brouillon');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_modeles_courriers')) {
            Schema::create('dae_modeles_courriers', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->string('nom');
                $table->string('type')->nullable();
                $table->text('contenu');
                $table->json('variables')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_conformite')) {
            Schema::create('dae_conformite', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('domaine')->nullable();
                $table->string('obligation')->nullable();
                $table->string('statut')->default('a_verifier');
                $table->date('echeance')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_audit_logs')) {
            Schema::create('dae_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('action')->nullable();
                $table->string('modele')->nullable();
                $table->string('modele_id')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_office_supplies')) {
            Schema::create('dae_office_supplies', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->string('reference')->nullable();
                $table->integer('stock')->default(0);
                $table->integer('seuil_alerte')->default(5);
                $table->string('unite')->default('pcs');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_office_supply_requests')) {
            Schema::create('dae_office_supply_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('statut')->default('en_attente');
                $table->json('items')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dae_personnel_dossiers')) {
            Schema::create('dae_personnel_dossiers', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('poste')->nullable();
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->date('date_embauche')->nullable();
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        // ── GEL (Comptabilité / Cabinet) ──────────────────────────────────────

        if (!Schema::hasTable('gel_tasks')) {
            Schema::create('gel_tasks', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('created_by')->nullable()->index();
                $table->uuid('assigned_to')->nullable()->index();
                $table->string('titre');
                $table->text('description')->nullable();
                $table->string('statut')->default('a_faire');
                $table->string('priorite')->default('moyenne');
                $table->date('date_echeance')->nullable();
                $table->timestamp('termine_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_task_attachments')) {
            Schema::create('gel_task_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('gel_tasks')->cascadeOnDelete();
                $table->uuid('user_id')->nullable()->index();
                $table->string('nom');
                $table->string('fichier_path');
                $table->string('mime_type')->nullable();
                $table->bigInteger('taille')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_task_comments')) {
            Schema::create('gel_task_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('gel_tasks')->cascadeOnDelete();
                $table->uuid('user_id')->nullable()->index();
                $table->text('contenu');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_audit_logs')) {
            Schema::create('gel_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('action')->nullable();
                $table->string('modele')->nullable();
                $table->string('modele_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_entreprises')) {
            // Already exists as 'entreprises', alias view not needed — skip
        }

        if (!Schema::hasTable('gel_depenses')) {
            Schema::create('gel_depenses', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('categorie')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->date('date_depense')->nullable();
                $table->text('description')->nullable();
                $table->string('statut')->default('en_attente');
                $table->string('justificatif_path')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_devis')) {
            Schema::create('gel_devis', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('numero')->nullable();
                $table->date('date_devis')->nullable();
                $table->date('date_validite')->nullable();
                $table->decimal('total_ht', 15, 2)->default(0);
                $table->decimal('total_ttc', 15, 2)->default(0);
                $table->string('statut')->default('brouillon');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_bons_commande')) {
            Schema::create('gel_bons_commande', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('numero')->nullable();
                $table->date('date_commande')->nullable();
                $table->decimal('total_ht', 15, 2)->default(0);
                $table->decimal('total_ttc', 15, 2)->default(0);
                $table->string('statut')->default('en_attente');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_produits')) {
            Schema::create('gel_produits', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->string('reference')->nullable();
                $table->text('description')->nullable();
                $table->decimal('prix_ht', 15, 2)->default(0);
                $table->string('unite')->default('pcs');
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_rapprochements')) {
            Schema::create('gel_rapprochements', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->string('statut')->default('en_cours');
                $table->decimal('solde_releve', 15, 2)->default(0);
                $table->decimal('solde_comptable', 15, 2)->default(0);
                $table->decimal('ecart', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_saved_reports')) {
            Schema::create('gel_saved_reports', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('user_id')->nullable()->index();
                $table->string('titre');
                $table->string('type')->nullable();
                $table->json('parametres')->nullable();
                $table->string('fichier_path')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_conformite')) {
            Schema::create('gel_conformite', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('domaine')->nullable();
                $table->string('obligation');
                $table->string('statut')->default('non_verifie');
                $table->date('echeance')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_conformite_actions')) {
            Schema::create('gel_conformite_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conformite_id')->nullable()->constrained('gel_conformite')->nullOnDelete();
                $table->uuid('user_id')->nullable()->index();
                $table->text('action');
                $table->string('statut')->default('a_faire');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_subscriptions')) {
            Schema::create('gel_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->uuid('entreprise_id')->nullable()->index();
                $table->string('plan')->default('starter');
                $table->string('statut')->default('actif');
                $table->date('debut_le')->nullable();
                $table->date('fin_le')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_bulletins')) {
            Schema::create('gel_bulletins', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->uuid('salarie_id')->nullable()->index();
                $table->string('periode')->nullable();
                $table->decimal('salaire_base', 15, 2)->default(0);
                $table->decimal('net_a_payer', 15, 2)->default(0);
                $table->string('statut')->default('brouillon');
                $table->string('fichier_path')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_salaries')) {
            Schema::create('gel_salaries', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('matricule')->nullable();
                $table->string('poste')->nullable();
                $table->decimal('salaire_base', 15, 2)->default(0);
                $table->date('date_embauche')->nullable();
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_profil_professionnels')) {
            Schema::create('gel_profil_professionnels', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->string('specialite')->nullable();
                $table->string('certifications')->nullable();
                $table->text('bio')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_factures')) {
            Schema::create('gel_factures', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('numero')->nullable();
                $table->date('date_facture')->nullable();
                $table->date('date_echeance')->nullable();
                $table->decimal('montant_ht', 15, 2)->default(0);
                $table->decimal('montant_tva', 15, 2)->default(0);
                $table->decimal('montant_ttc', 15, 2)->default(0);
                $table->string('statut')->default('brouillon');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_factures_honoraires')) {
            Schema::create('gel_factures_honoraires', function (Blueprint $table) {
                $table->id();
                $table->uuid('cabinet_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('numero')->nullable();
                $table->date('date_facture')->nullable();
                $table->decimal('montant_ht', 15, 2)->default(0);
                $table->decimal('montant_ttc', 15, 2)->default(0);
                $table->string('statut')->default('brouillon');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_facture_lignes')) {
            Schema::create('gel_facture_lignes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facture_id')->nullable()->constrained('gel_factures')->nullOnDelete();
                $table->string('designation');
                $table->decimal('quantite', 10, 2)->default(1);
                $table->decimal('prix_unitaire', 15, 2)->default(0);
                $table->decimal('tva', 5, 2)->default(0);
                $table->decimal('montant_ht', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gel_fiscal_parameters')) {
            Schema::create('gel_fiscal_parameters', function (Blueprint $table) {
                $table->id();
                $table->uuid('entreprise_id')->nullable()->index();
                $table->string('pays_code')->default('BJ');
                $table->string('regime_fiscal')->nullable();
                $table->string('periodicite_tva')->nullable();
                $table->json('parametres')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('regle_fiscale')) {
            // Already exists as 'regles_fiscales' - skip
        }

        if (!Schema::hasTable('gel_account_types')) {
            Schema::create('gel_account_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('libelle');
                $table->string('classe')->nullable();
                $table->timestamps();
            });
        }

        // ── RH ────────────────────────────────────────────────────────────────

        if (!Schema::hasTable('rh_contracts')) {
            Schema::create('rh_contracts', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->foreignId('salarie_id')->nullable()->constrained('gel_salaries')->nullOnDelete();
                $table->string('type')->nullable();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->decimal('salaire', 15, 2)->default(0);
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_leave_requests')) {
            Schema::create('rh_leave_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->foreignId('salarie_id')->nullable()->constrained('gel_salaries')->nullOnDelete();
                $table->string('type_conge')->nullable();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->string('statut')->default('en_attente');
                $table->text('motif')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_payrolls')) {
            Schema::create('rh_payrolls', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('periode')->nullable();
                $table->decimal('total_salaires', 15, 2)->default(0);
                $table->decimal('total_charges', 15, 2)->default(0);
                $table->string('statut')->default('brouillon');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_attendance')) {
            Schema::create('rh_attendance', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->foreignId('salarie_id')->nullable()->constrained('gel_salaries')->nullOnDelete();
                $table->date('date_presence')->nullable();
                $table->time('heure_arrivee')->nullable();
                $table->time('heure_depart')->nullable();
                $table->string('statut')->default('present');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_expenses')) {
            Schema::create('rh_expenses', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->foreignId('salarie_id')->nullable()->constrained('gel_salaries')->nullOnDelete();
                $table->string('categorie')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->date('date_depense')->nullable();
                $table->string('statut')->default('en_attente');
                $table->string('justificatif_path')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_trainings')) {
            Schema::create('rh_trainings', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('intitule');
                $table->string('organisme')->nullable();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->string('statut')->default('planifie');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_alerts')) {
            Schema::create('rh_alerts', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('type')->nullable();
                $table->text('message');
                $table->string('statut')->default('non_lu');
                $table->timestamp('echeance_at')->nullable();
                $table->timestamps();
            });
        }

        // ── Legal ─────────────────────────────────────────────────────────────

        if (!Schema::hasTable('legal_dossiers')) {
            Schema::create('legal_dossiers', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('intitule');
                $table->string('type')->nullable();
                $table->string('statut')->default('ouvert');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_contracts')) {
            Schema::create('legal_contracts', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('intitule');
                $table->string('type')->nullable();
                $table->date('date_signature')->nullable();
                $table->date('date_expiration')->nullable();
                $table->string('statut')->default('actif');
                $table->string('fichier_path')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_contract_signatures')) {
            Schema::create('legal_contract_signatures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->nullable()->constrained('legal_contracts')->nullOnDelete();
                $table->string('signataire_nom');
                $table->string('signataire_email')->nullable();
                $table->timestamp('signed_at')->nullable();
                $table->string('statut')->default('en_attente');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_assemblies')) {
            Schema::create('legal_assemblies', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('type')->nullable();
                $table->date('date_assemblee')->nullable();
                $table->string('lieu')->nullable();
                $table->text('ordre_du_jour')->nullable();
                $table->text('deliberations')->nullable();
                $table->string('statut')->default('planifie');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_registres')) {
            Schema::create('legal_registres', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('type');
                $table->string('reference')->nullable();
                $table->date('date_ouverture')->nullable();
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_compliance')) {
            Schema::create('legal_compliance', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('domaine')->nullable();
                $table->string('obligation');
                $table->string('statut')->default('a_verifier');
                $table->date('echeance')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_litigations')) {
            Schema::create('legal_litigations', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('intitule');
                $table->string('type')->nullable();
                $table->string('partie_adverse')->nullable();
                $table->string('statut')->default('en_cours');
                $table->date('date_ouverture')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_acts_library')) {
            Schema::create('legal_acts_library', function (Blueprint $table) {
                $table->id();
                $table->string('titre');
                $table->string('categorie')->nullable();
                $table->text('contenu')->nullable();
                $table->string('fichier_path')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_veille')) {
            Schema::create('legal_veille', function (Blueprint $table) {
                $table->id();
                $table->string('titre');
                $table->string('source')->nullable();
                $table->text('contenu')->nullable();
                $table->date('date_publication')->nullable();
                $table->string('categorie')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_company_infos')) {
            Schema::create('legal_company_infos', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('forme_juridique')->nullable();
                $table->decimal('capital', 15, 2)->nullable();
                $table->string('rccm')->nullable();
                $table->string('ifu')->nullable();
                $table->date('date_creation')->nullable();
                $table->json('dirigeants')->nullable();
                $table->json('associes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_audit_log')) {
            Schema::create('legal_audit_log', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('action');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // ── Cabinet ───────────────────────────────────────────────────────────

        if (!Schema::hasTable('cabinet_documents')) {
            Schema::create('cabinet_documents', function (Blueprint $table) {
                $table->id();
                $table->uuid('cabinet_id')->nullable()->index();
                $table->string('nom');
                $table->string('categorie')->nullable();
                $table->string('fichier_path')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cabinet_invitations')) {
            Schema::create('cabinet_invitations', function (Blueprint $table) {
                $table->id();
                $table->uuid('cabinet_id')->nullable()->index();
                $table->uuid('invited_by')->nullable()->index();
                $table->string('email');
                $table->string('role')->nullable();
                $table->string('token')->nullable()->unique();
                $table->string('statut')->default('en_attente');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cabinet_subscription_invoices')) {
            Schema::create('cabinet_subscription_invoices', function (Blueprint $table) {
                $table->id();
                $table->uuid('cabinet_id')->nullable()->index();
                $table->string('numero')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->string('statut')->default('en_attente');
                $table->date('date_facture')->nullable();
                $table->date('date_echeance')->nullable();
                $table->timestamps();
            });
        }

        // ── Divers ────────────────────────────────────────────────────────────

        if (!Schema::hasTable('ai_learning_log')) {
            Schema::create('ai_learning_log', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->string('type')->nullable();
                $table->text('input')->nullable();
                $table->text('output')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('approval_steps_log')) {
            Schema::create('approval_steps_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('approval_request_id')->nullable()->constrained('approval_requests')->nullOnDelete();
                $table->uuid('approver_id')->nullable()->index();
                $table->string('action')->nullable();
                $table->text('commentaire')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('client_accounting_modules')) {
            Schema::create('client_accounting_modules', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->string('module_code');
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('client_modules')) {
            Schema::create('client_modules', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->string('module_code');
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('client_pole')) {
            Schema::create('client_pole', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->unsignedBigInteger('pole_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('client_service')) {
            Schema::create('client_service', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->index();
                $table->unsignedBigInteger('service_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('comptable_clients')) {
            Schema::create('comptable_clients', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->index();
                $table->uuid('client_id')->index();
                $table->string('role')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('document_audit_log')) {
            Schema::create('document_audit_log', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
                $table->string('action');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('document_versions')) {
            Schema::create('document_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
                $table->uuid('user_id')->nullable()->index();
                $table->integer('version_num')->default(1);
                $table->string('fichier_path');
                $table->text('commentaire')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('email_modeles')) {
            Schema::create('email_modeles', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->string('nom');
                $table->string('sujet')->nullable();
                $table->text('corps');
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('emails_recus')) {
            Schema::create('emails_recus', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('from_email')->nullable();
                $table->string('subject')->nullable();
                $table->text('body')->nullable();
                $table->boolean('est_lu')->default(false);
                $table->timestamp('received_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ia_messages')) {
            Schema::create('ia_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->nullable()->constrained('chat_conversations')->nullOnDelete();
                $table->string('role')->default('user'); // user | assistant
                $table->text('content');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('independant_comptable_clients')) {
            Schema::create('independant_comptable_clients', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->index();
                $table->uuid('client_id')->index();
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('internal_accounts')) {
            Schema::create('internal_accounts', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->string('type')->nullable();
                $table->decimal('solde', 15, 2)->default(0);
                $table->string('devise')->default('XOF');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('it_knowledge_base')) {
            Schema::create('it_knowledge_base', function (Blueprint $table) {
                $table->id();
                $table->string('titre');
                $table->string('categorie')->nullable();
                $table->text('contenu');
                $table->string('statut')->default('publie');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('module_cabinets')) {
            Schema::create('module_cabinets', function (Blueprint $table) {
                $table->id();
                $table->uuid('cabinet_id')->nullable()->index();
                $table->string('module_code');
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permission_field_restrictions')) {
            Schema::create('permission_field_restrictions', function (Blueprint $table) {
                $table->id();
                $table->uuid('role_id')->nullable()->index();
                $table->string('modele');
                $table->string('champ');
                $table->string('action')->default('read');
                $table->boolean('interdit')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('portal_contacts')) {
            Schema::create('portal_contacts', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->string('poste')->nullable();
                $table->boolean('est_principal')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('transmissions_internes')) {
            Schema::create('transmissions_internes', function (Blueprint $table) {
                $table->id();
                $table->uuid('from_user_id')->nullable()->index();
                $table->uuid('to_user_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('objet')->nullable();
                $table->text('message')->nullable();
                $table->boolean('est_lu')->default(false);
                $table->string('statut')->default('envoye');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_dashboard_config')) {
            Schema::create('user_dashboard_config', function (Blueprint $table) {
                $table->id();
                $table->uuid('user_id')->index();
                $table->json('widgets')->nullable();
                $table->json('layout')->nullable();
                $table->timestamps();
            });
        }

        // ── Accounting sectorielles ────────────────────────────────────────────

        if (!Schema::hasTable('accounting_budget_lines')) {
            Schema::create('accounting_budget_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('budget_id')->nullable()->index();
                $table->string('compte')->nullable();
                $table->decimal('montant_prevu', 15, 2)->default(0);
                $table->decimal('montant_reel', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_commissions')) {
            Schema::create('accounting_commissions', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('agent')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->decimal('taux', 5, 2)->default(0);
                $table->date('date_commission')->nullable();
                $table->string('statut')->default('en_attente');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_cotisations')) {
            Schema::create('accounting_cotisations', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('membre')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->date('date_cotisation')->nullable();
                $table->string('statut')->default('paye');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_emballages')) {
            Schema::create('accounting_emballages', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('reference')->nullable();
                $table->string('type')->nullable();
                $table->integer('quantite')->default(0);
                $table->decimal('valeur_unitaire', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_grilles_tarifaires')) {
            Schema::create('accounting_grilles_tarifaires', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('designation');
                $table->decimal('prix', 15, 2)->default(0);
                $table->string('categorie')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_hotel_chambres')) {
            Schema::create('accounting_hotel_chambres', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('numero');
                $table->string('type')->nullable();
                $table->decimal('tarif', 15, 2)->default(0);
                $table->string('statut')->default('libre');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_hotel_reservations')) {
            Schema::create('accounting_hotel_reservations', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->foreignId('chambre_id')->nullable()->constrained('accounting_hotel_chambres')->nullOnDelete();
                $table->string('client_nom')->nullable();
                $table->date('date_arrivee')->nullable();
                $table->date('date_depart')->nullable();
                $table->string('statut')->default('reservee');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_hotel_factures')) {
            Schema::create('accounting_hotel_factures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->nullable()->constrained('accounting_hotel_reservations')->nullOnDelete();
                $table->decimal('total', 15, 2)->default(0);
                $table->string('statut')->default('en_attente');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_location_biens')) {
            Schema::create('accounting_location_biens', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('designation');
                $table->string('locataire')->nullable();
                $table->decimal('loyer_mensuel', 15, 2)->default(0);
                $table->date('date_debut')->nullable();
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_mobile_transactions')) {
            Schema::create('accounting_mobile_transactions', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('type')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->string('numero_mobile')->nullable();
                $table->string('operateur')->nullable();
                $table->timestamp('transaction_at')->nullable();
                $table->string('statut')->default('effectue');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_morgue_depots')) {
            Schema::create('accounting_morgue_depots', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom_defunt')->nullable();
                $table->date('date_depot')->nullable();
                $table->date('date_sortie')->nullable();
                $table->string('statut')->default('en_depot');
                $table->decimal('frais', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_morgue_factures')) {
            Schema::create('accounting_morgue_factures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('depot_id')->nullable()->constrained('accounting_morgue_depots')->nullOnDelete();
                $table->decimal('total', 15, 2)->default(0);
                $table->string('statut')->default('en_attente');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_pressing_commandes')) {
            Schema::create('accounting_pressing_commandes', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('client_nom')->nullable();
                $table->integer('nombre_pieces')->default(0);
                $table->decimal('montant', 15, 2)->default(0);
                $table->date('date_depot')->nullable();
                $table->date('date_livraison')->nullable();
                $table->string('statut')->default('en_cours');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_quittances')) {
            Schema::create('accounting_quittances', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('numero')->nullable();
                $table->string('beneficiaire')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->date('date_quittance')->nullable();
                $table->string('motif')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_scolaire_eleves')) {
            Schema::create('accounting_scolaire_eleves', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('classe')->nullable();
                $table->string('matricule')->nullable();
                $table->string('statut')->default('inscrit');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_scolaire_factures')) {
            Schema::create('accounting_scolaire_factures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eleve_id')->nullable()->constrained('accounting_scolaire_eleves')->nullOnDelete();
                $table->string('type_frais')->nullable();
                $table->decimal('montant', 15, 2)->default(0);
                $table->string('statut')->default('en_attente');
                $table->date('date_facture')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_tontines')) {
            Schema::create('accounting_tontines', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('nom');
                $table->decimal('mise', 15, 2)->default(0);
                $table->string('periodicite')->default('mensuel');
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_transit_dossiers')) {
            Schema::create('accounting_transit_dossiers', function (Blueprint $table) {
                $table->id();
                $table->uuid('client_id')->nullable()->index();
                $table->string('reference');
                $table->string('nature_marchandise')->nullable();
                $table->decimal('valeur_douane', 15, 2)->default(0);
                $table->date('date_arrivee')->nullable();
                $table->string('statut')->default('en_cours');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'dae_agenda_events', 'dae_courriers', 'dae_contrats', 'dae_documents',
            'dae_document_dossiers', 'dae_taches', 'dae_meeting_minutes', 'dae_messages',
            'dae_emails', 'dae_rapports', 'dae_modeles_courriers', 'dae_conformite',
            'dae_audit_logs', 'dae_office_supplies', 'dae_office_supply_requests',
            'dae_personnel_dossiers', 'gel_task_comments', 'gel_task_attachments',
            'gel_tasks', 'gel_audit_logs', 'gel_depenses', 'gel_devis', 'gel_bons_commande',
            'gel_produits', 'gel_rapprochements', 'gel_saved_reports', 'gel_conformite_actions',
            'gel_conformite', 'gel_subscriptions', 'gel_bulletins', 'gel_salaries',
            'gel_profil_professionnels', 'gel_facture_lignes', 'gel_factures_honoraires',
            'gel_factures', 'gel_fiscal_parameters', 'gel_account_types',
            'rh_alerts', 'rh_trainings', 'rh_expenses', 'rh_attendance',
            'rh_payrolls', 'rh_leave_requests', 'rh_contracts',
            'legal_audit_log', 'legal_company_infos', 'legal_veille', 'legal_acts_library',
            'legal_litigations', 'legal_compliance', 'legal_registres',
            'legal_assemblies', 'legal_contract_signatures', 'legal_contracts', 'legal_dossiers',
            'cabinet_subscription_invoices', 'cabinet_invitations', 'cabinet_documents',
            'ai_learning_log', 'approval_steps_log', 'client_accounting_modules',
            'client_modules', 'client_pole', 'client_service', 'comptable_clients',
            'document_audit_log', 'document_versions', 'email_modeles', 'emails_recus',
            'ia_messages', 'independant_comptable_clients', 'internal_accounts',
            'it_knowledge_base', 'module_cabinets', 'permission_field_restrictions',
            'portal_contacts', 'transmissions_internes', 'user_dashboard_config',
            'accounting_transit_dossiers', 'accounting_tontines', 'accounting_scolaire_factures',
            'accounting_scolaire_eleves', 'accounting_quittances', 'accounting_pressing_commandes',
            'accounting_morgue_factures', 'accounting_morgue_depots', 'accounting_mobile_transactions',
            'accounting_location_biens', 'accounting_hotel_factures', 'accounting_hotel_reservations',
            'accounting_hotel_chambres', 'accounting_grilles_tarifaires', 'accounting_emballages',
            'accounting_cotisations', 'accounting_commissions', 'accounting_budget_lines',
        ];
        foreach ($tables as $t) {
            Schema::dropIfExists($t);
        }
    }
};
