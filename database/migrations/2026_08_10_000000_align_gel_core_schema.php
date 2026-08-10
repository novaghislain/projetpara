<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Align gel_entreprises with specifications
        if (Schema::hasTable('gel_entreprises')) {
            Schema::table('gel_entreprises', function (Blueprint $table) {
                if (!Schema::hasColumn('gel_entreprises', 'pays_code')) {
                    $table->char('pays_code', 2)->default('BJ')->after('nom');
                }
                if (!Schema::hasColumn('gel_entreprises', 'rccm')) {
                    $table->string('rccm', 100)->nullable()->after('ifu');
                }
                if (!Schema::hasColumn('gel_entreprises', 'regime_fiscal')) {
                    $table->string('regime_fiscal')->nullable();
                }
                if (!Schema::hasColumn('gel_entreprises', 'modele_usage')) {
                    $table->string('modele_usage')->nullable();
                }
                if (!Schema::hasColumn('gel_entreprises', 'statut_abonnement')) {
                    $table->string('statut_abonnement')->default('ACTIF');
                }
            });
        }

        // Table P1.2: plan_comptable_syscohada
        if (!Schema::hasTable('plan_comptable_syscohada')) {
            Schema::create('plan_comptable_syscohada', function (Blueprint $table) {
                $table->id();
                $table->string('numero', 20)->unique();
                $table->string('libelle');
                $table->integer('classe');
                $table->boolean('est_actif')->default(true);
                $table->timestamps();
            });
        }

        // Table P1.2: regles_fiscales
        if (!Schema::hasTable('regles_fiscales')) {
            Schema::create('regles_fiscales', function (Blueprint $table) {
                $table->id();
                $table->char('pays_code', 2)->default('BJ');
                $table->string('type_impot'); // IS, ITS, TVA, TPS, etc.
                $table->string('nom');
                $table->decimal('taux', 5, 2)->nullable(); // Percentage e.g. 18.00
                $table->json('bareme')->nullable(); // For graduated taxes like ITS
                $table->text('description')->nullable();
                $table->date('date_effet')->nullable();
                $table->boolean('est_actif')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_fiscales');
        Schema::dropIfExists('plan_comptable_syscohada');
        
        if (Schema::hasTable('gel_entreprises')) {
            Schema::table('gel_entreprises', function (Blueprint $table) {
                $table->dropColumn(['pays_code', 'rccm', 'regime_fiscal', 'modele_usage', 'statut_abonnement']);
            });
        }
    }
};
