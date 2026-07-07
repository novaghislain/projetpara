<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');
        $teamForeignKey = config('permission.team_foreign_key');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // 1. gel_permissions
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('guard_name', 255)->default('web');
            $table->string('module', 100)->nullable()->comment('Module métier (comptabilite, paie, fiscalite, crm, client, ged, ia, admin, entreprise, cpa)');
            $table->string('label_fr', 255)->nullable()->comment('Libellé français');
            $table->text('description')->nullable();
            $table->string('portail', 50)->nullable()->comment('gel, entreprise, cpa');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // 2. gel_roles
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $teamForeignKey) {
            $table->bigIncrements('id');
            if ($teams && $teamForeignKey) {
                $table->unsignedBigInteger($teamForeignKey)->nullable()->default(null);
                $table->index($teamForeignKey, 'gel_roles_cabinet_id_index');
            }
            $table->string('name', 255);
            $table->string('guard_name', 255)->default('web');
            $table->string('module', 100)->nullable()->comment('Module associé');
            $table->string('label_fr', 255)->nullable()->comment('Libellé français');
            $table->text('description')->nullable();
            $table->integer('level')->default(99)->comment('Niveau hiérarchique (0=super_admin, 1=directeur, 2=senior, 3=junior, 4=stagiaire, 5=auditeur)');
            $table->string('portail', 50)->nullable()->comment('gel, entreprise, cpa');
            $table->timestamps();

            $table->unique(['name', 'guard_name', $teamForeignKey]);
        });

        // 3. gel_model_has_permissions
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams, $teamForeignKey) {
            $table->unsignedBigInteger('permission_id');

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(
                ['permission_id', $columnNames['model_morph_key'], 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        // 4. gel_model_has_roles
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams, $teamForeignKey) {
            $table->unsignedBigInteger('role_id');

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(
                ['role_id', $columnNames['model_morph_key'], 'model_type'],
                'model_has_roles_role_model_type_primary'
            );
        });

        // 5. gel_role_has_permissions
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_role_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
