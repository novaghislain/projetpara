<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupFictitiousData extends Command
{
    protected $signature = 'app:cleanup-fictitious-data';
    protected $description = 'Supprime toutes les données fictives et ne garde que la vraie entreprise (ID 1)';

    public function handle()
    {
        $realClientId = 1;
        $deleted = [];

        // Delete from clients table
        $deleted['clients'] = DB::table('clients')->where('id', '!=', $realClientId)->delete();

        // Delete from gel_clients table
        $deleted['gel_clients'] = DB::table('gel_clients')->where('id', '!=', $realClientId)->delete();

        // Delete associated users
        $deleted['users_clients'] = DB::table('users')->where('client_id', '!=', $realClientId)->whereNotNull('client_id')->delete();

        // Delete fake system users (no client_id but fake emails)
        $deleted['fake_system_users'] = DB::table('users')
            ->whereNull('client_id')
            ->whereNotIn('email', ['admin@gel.cabinet', 'directeur@gel.cabinet', 'alice@gel.cabinet', 'bob@gel.cabinet', 'secretaire@monprojet.com'])
            ->delete();

        // Standard tables
        $tablesWithClient = [
            'dae_courriers', 'dae_agenda_events', 'dae_documents', 'contacts',
            'accounting_entries', 'accounting_invoices',
            'rh_employees', 'rh_leaves',
            'legal_documents', 'legal_cases', 'gel_client_invitations', 'tasks'
        ];

        foreach ($tablesWithClient as $table) {
            try {
                $count = DB::table($table)->where('client_id', '!=', $realClientId)->delete();
                if ($count > 0) $deleted[$table] = $count;
            } catch (\Exception $e) {}
        }

        // Catch any other table that has client_id
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $tableInfo) {
            $table = array_values((array)$tableInfo)[0];
            try {
                $columns = DB::select('SHOW COLUMNS FROM ' . $table);
                foreach ($columns as $column) {
                    if ($column->Field === 'client_id' && !in_array($table, $tablesWithClient) && !in_array($table, ['clients', 'gel_clients', 'users'])) {
                        $count = DB::table($table)->where('client_id', '!=', $realClientId)->delete();
                        if ($count > 0) $deleted[$table] = $count;
                    }
                }
            } catch (\Exception $e) {}
        }

        $this->info(json_encode($deleted, JSON_PRETTY_PRINT));
    }
}
