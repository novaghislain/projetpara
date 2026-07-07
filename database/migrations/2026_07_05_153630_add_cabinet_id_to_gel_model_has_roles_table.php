<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le champ cabinet_id requis par Spatie Permission (team_foreign_key)
     * pour la gestion multi-cabinet des rôles et permissions.
     */
    public function up(): void
    {
        Schema::table('gel_model_has_roles', function (Blueprint $table) {
            if (!Schema::hasColumn('gel_model_has_roles', 'cabinet_id')) {
                $table->foreignId('cabinet_id')
                    ->nullable()
                    ->constrained('tenants')
                    ->cascadeOnDelete()
                    ->after('role_id');
            }
        });

        // Spatie utilise aussi la table model_has_permissions dans le même schéma
        if (Schema::hasTable('gel_model_has_permissions')) {
            Schema::table('gel_model_has_permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('gel_model_has_permissions', 'cabinet_id')) {
                    $table->foreignId('cabinet_id')
                        ->nullable()
                        ->constrained('tenants')
                        ->cascadeOnDelete()
                        ->after('permission_id');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('gel_model_has_roles', function (Blueprint $table) {
            if (Schema::hasColumn('gel_model_has_roles', 'cabinet_id')) {
                $table->dropForeign(['cabinet_id']);
                $table->dropColumn('cabinet_id');
            }
        });

        if (Schema::hasTable('gel_model_has_permissions')) {
            Schema::table('gel_model_has_permissions', function (Blueprint $table) {
                if (Schema::hasColumn('gel_model_has_permissions', 'cabinet_id')) {
                    $table->dropForeign(['cabinet_id']);
                    $table->dropColumn('cabinet_id');
                }
            });
        }
    }
};
