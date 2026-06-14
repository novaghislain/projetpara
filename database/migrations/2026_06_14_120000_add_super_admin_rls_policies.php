<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $rlsTables = [
        'accounting_accounts',
        'accounting_journals',
        'client_contacts',
        'client_folders',
        'client_pole',
        'client_service',
        'company_employees',
        'company_expenses',
        'company_invoice_items',
        'company_invoices',
        'company_leave_requests',
        'company_payments',
        'company_contracts',
        'company_legal_cases',
        'company_projects',
        'company_project_tasks',
        'company_crm_contacts',
        'company_crm_deals',
        'company_crm_interactions',
        'company_ai_chats',
        'documents',
        'document_versions',
        'document_audit_log',
        'erp_invoices',
        'licenses',
        'missions',
        'notifications',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // Pour chaque table, on ajoute une policy spécifique pour le super admin (client_id = 0)
        // Cela permet à l'utilisateur système (client_id=0 ou NULL) de gérer TOUS les locataires
        foreach ($this->rlsTables as $table) {
            try {
                DB::statement("
                    CREATE POLICY super_admin_all_{$table} ON \"{$table}\"
                    FOR ALL
                    USING (app.current_client_id() = 0)
                    WITH CHECK (app.current_client_id() = 0)
                ");
            } catch (\Exception $e) {
                // Ignorer si la politique existe déjà
                echo "Note: politique super_admin_all_{$table} déjà présente ou erreur: " . $e->getMessage() . "\n";
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->rlsTables as $table) {
            try {
                DB::statement("DROP POLICY IF EXISTS super_admin_all_{$table} ON \"{$table}\"");
            } catch (\Exception $e) {
                // Ignorer
            }
        }
    }
};
