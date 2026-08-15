<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Utilisateurs
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('mot_de_passe_hash');
            $table->string('nom');
            $table->string('telephone')->nullable();
            $table->boolean('mfa_active')->default(false);
            $table->string('mfa_secret')->nullable();
            $table->string('statut')->default('actif');
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('supprime_le')->nullable();
            
            // Pour compatibilité Laravel Auth
            $table->string('password')->nullable();
            $table->rememberToken();
        });

        // 2. Entreprises
        Schema::create('entreprises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('raison_sociale');
            $table->char('pays_code', 2);
            $table->string('ifu')->nullable();
            $table->string('rccm')->nullable();
            $table->string('secteur_activite')->nullable();
            $table->string('regime_fiscal');
            $table->string('modele_usage');
            $table->string('statut_abonnement')->default('essai');
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('supprime_le')->nullable();
        });

        // 3. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('libelle');
        });

        // 4. Affectations (Utilisateur <-> Entreprise <-> Role)
        Schema::create('affectations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('utilisateur_id')->constrained('utilisateurs');
            $table->foreignUuid('entreprise_id')->constrained('entreprises');
            $table->foreignUuid('role_id')->constrained('roles');
            $table->string('modele');
            $table->string('statut')->default('active');
            $table->timestamp('expire_le')->nullable();
            $table->timestamp('cree_le')->useCurrent();
            
            $table->unique(['utilisateur_id', 'entreprise_id', 'role_id']);
        });

        // 5. Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('affectation_id')->constrained('affectations')->cascadeOnDelete();
            $table->string('ressource');
            $table->string('action');
            $table->boolean('autorise')->default(false);

            $table->unique(['affectation_id', 'ressource', 'action']);
        });

        // 6. Abonnements
        Schema::create('abonnements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->nullable()->constrained('entreprises');
            $table->foreignUuid('payeur_utilisateur_id')->nullable()->constrained('utilisateurs');
            $table->string('plan');
            $table->json('services_inclus');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('essai');
        });
        
        // Modules activés par entreprise (Nouveau pour le Multi-Tenant à la carte)
        Schema::create('module_entreprise', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
            $table->string('module_code'); // ex: 'comptabilite', 'secretariat', 'paie'
            $table->boolean('actif')->default(true);
            $table->unique(['entreprise_id', 'module_code']);
        });

        // Sessions et Passwords resets (Laravel standard)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('module_entreprise');
        Schema::dropIfExists('abonnements');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('affectations');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('entreprises');
        Schema::dropIfExists('utilisateurs');
    }
};
