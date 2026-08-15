<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\AccountingJournalLine;
use App\Policies\ClientPolicy;
use App\Policies\JournalEntryPolicy;
use App\Policies\AiSuggestionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Les politiques (Policies) enregistrées pour l'application.
     */
    protected $policies = [
        Client::class                  => ClientPolicy::class,
        AccountingJournalLine::class   => JournalEntryPolicy::class,
    ];

    /**
     * Enregistre les Gates et Policies.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ─── Gates génériques ──────────────────────────────────────────

        // Gate : accès à un module métier
        Gate::define('module-access', function ($user, string $module) {
            return $user->hasModule($module);
        });

        // Gate : permission spécifique (module.action)
        Gate::define('permission', function ($user, string $permission) {
            return $user->hasPermissionTo($permission);
        });

        // Gate : action spécifique dans un module (module, action)
        Gate::define('module-action', function ($user, string $module, string $action) {
            return $user->canModule($module, $action);
        });

        // ─── Gates par module métier ────────────────────────────────────

        // Comptabilité
        Gate::define('compta-view', fn($user) => $user->hasPermissionTo('comptabilite.consulter'));
        Gate::define('compta-write', fn($user) => $user->hasPermissionTo('comptabilite.ecrire'));
        Gate::define('compta-validate', fn($user) => $user->hasPermissionTo('comptabilite.valider'));
        Gate::define('compta-export', fn($user) => $user->hasPermissionTo('comptabilite.exporter'));
        Gate::define('compta-close', fn($user) => $user->hasPermissionTo('comptabilite.cloturer'));

        // Paie
        Gate::define('paie-view', fn($user) => $user->hasPermissionTo('paie.consulter'));
        Gate::define('paie-payroll', fn($user) => $user->hasPermissionTo('paie.bulletin'));
        Gate::define('paie-cnss', fn($user) => $user->hasPermissionTo('paie.decla_cnss'));
        Gate::define('paie-its', fn($user) => $user->hasPermissionTo('paie.decla_its'));

        // Fiscalité
        Gate::define('fiscal-view', fn($user) => $user->hasPermissionTo('fiscalite.consulter'));
        Gate::define('fiscal-declare', fn($user) => $user->hasPermissionTo('fiscalite.declarer'));
        Gate::define('fiscal-tva', fn($user) => $user->hasPermissionTo('fiscalite.tva'));
        Gate::define('fiscal-emecef', fn($user) => $user->hasPermissionTo('fiscalite.e_mecef'));

        // Fiscalité — actions fines exigées CDC FD2 §22.1 (Action 3) / §6.3
        // Moteur fiscal (RegleFiscale) : création par Fiscaliste (en_attente_validation),
        // validation finale réservée au Super Administrateur (double contrôle §7.5).
        Gate::define('fiscal-regle-create',   fn($user) => $user->hasPermissionTo('fiscalite.parametrer_regle'));
        Gate::define('fiscal-regle-validate', fn($user) => $user->hasPermissionTo('fiscalite.valider_regle'));
        // Cycle de vie des déclarations : préparation → validation → téléversement.
        Gate::define('fiscal-declaration-prepare',  fn($user) => $user->hasPermissionTo('fiscalite.preparer_declaration'));
        Gate::define('fiscal-declaration-validate', fn($user) => $user->hasPermissionTo('fiscalite.valider_declaration'));
        Gate::define('fiscal-declaration-upload',   fn($user) => $user->hasPermissionTo('fiscalite.televerser_declaration'));

        // CRM
        Gate::define('crm-view', fn($user) => $user->hasPermissionTo('crm.consulter'));
        Gate::define('crm-assign', fn($user) => $user->hasPermissionTo('crm.assigner'));

        // IA
        Gate::define('ia-view', fn($user) => $user->hasPermissionTo('ia.consulter'));
        Gate::define('ia-suggest', fn($user) => $user->hasPermissionTo('ia.suggestion'));

        // Admin
        Gate::define('admin-access', fn($user) => $user->hasPermissionTo('admin.acces'));
        Gate::define('admin-config', fn($user) => $user->hasPermissionTo('admin.config'));
        Gate::define('admin-logs', fn($user) => $user->hasPermissionTo('admin.logs'));

        // Entreprise
        Gate::define('entreprise-dashboard', fn($user) => $user->hasPermissionTo('entreprise.tableau_bord'));
        Gate::define('entreprise-compta', fn($user) => $user->hasPermissionTo('entreprise.comptabilite'));
        Gate::define('entreprise-paie', fn($user) => $user->hasPermissionTo('entreprise.paie'));

        // CPA
        Gate::define('cpa-documents', fn($user) => $user->hasPermissionTo('cpa.documents'));
        Gate::define('cpa-declarations', fn($user) => $user->hasPermissionTo('cpa.declarations'));

        // ─── Gates multi-tenant ────────────────────────────────────────

        // Vérifie que l'utilisateur agit dans son propre cabinet
        Gate::define('same-cabinet', function ($user, $model) {
            $cabinetId = $user->cabinet_id ?? session('current_cabinet_id');
            $modelCabinetId = $model->cabinet_id ?? $model->company?->cabinet_id;
            if (! $cabinetId || ! $modelCabinetId) {
                return $user->isSuperAdmin();
            }
            return (int) $cabinetId === (int) $modelCabinetId;
        });

        // Vérifie que l'utilisateur agit dans le contexte de son client actif
        Gate::define('same-client', function ($user, $model) {
            $clientId = $user->active_client_id ?? $user->client_id;
            if (! $clientId) {
                return $user->isSuperAdmin() || $user->isComptable();
            }
            $modelClientId = $model->client_id ?? ($model->client?->id);
            return $modelClientId && (int) $clientId === (int) $modelClientId;
        });

        // Vérifie que l'utilisateur est Super Admin
        Gate::define('super-admin', fn($user) => $user->isSuperAdmin());
    }
}
