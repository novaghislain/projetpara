<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tables métier isolées par tenant (client_id).
     * Les tables systèmes (users, roles, permissions, services…)
     * sont protégées au niveau application, pas par RLS.
     */
    private array $rlsTables = [
        'accounting_accounts',
        'accounting_journals',
        'client_contacts',
        'client_folders',
        'client_pole',
        'client_service',
        'documents',
        'erp_invoices',
        'licenses',
        'missions',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // 1. Créer le schéma app s'il n'existe pas
        DB::statement("CREATE SCHEMA IF NOT EXISTS app;");

        // 2. Créer la fonction utilitaire pour récupérer le client_id courant
        DB::statement("
            CREATE OR REPLACE FUNCTION app.current_client_id()
            RETURNS INTEGER
            LANGUAGE SQL
            STABLE
            AS \$\$
                SELECT COALESCE(NULLIF(current_setting('app.client_id', true), ''), '0')::INTEGER;
            \$\$;
        ");

        // 3. Activer RLS + politique sur chaque table métier
        //    FORCE ROW LEVEL SECURITY : l'owner (gel_app) est aussi soumis au RLS.
        //    Les seeders doivent positionner SET app.client_id avant d'insérer.
        foreach ($this->rlsTables as $table) {
            DB::statement("ALTER TABLE \"{$table}\" ENABLE ROW LEVEL SECURITY;");
            DB::statement("ALTER TABLE \"{$table}\" FORCE ROW LEVEL SECURITY;");
            DB::statement("
                CREATE POLICY tenant_isolation_{$table} ON \"{$table}\"
                FOR ALL
                USING (client_id = app.current_client_id())
                WITH CHECK (client_id = app.current_client_id());
            ");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->rlsTables as $table) {
            DB::statement("DROP POLICY IF EXISTS tenant_isolation_{$table} ON \"{$table}\";");
            DB::statement("ALTER TABLE \"{$table}\" DISABLE ROW LEVEL SECURITY;");
        }
        DB::statement("DROP FUNCTION IF EXISTS app.current_client_id();");
        DB::statement("DROP SCHEMA IF EXISTS app;");
    }
};
