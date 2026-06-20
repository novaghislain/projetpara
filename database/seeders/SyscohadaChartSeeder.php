<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyscohadaChartSeeder extends Seeder
{
    /**
     * Chemins vers le fichier JSON du plan comptable.
     */
    const JSON_PATH = 'database/data/syscohada_accounts.json';

    /**
     * Execute the seeder — crée les comptes de référence (client_id = 0).
     */
    public function run(): void
    {
        $accounts = $this->loadAccounts();
        if (empty($accounts)) {
            $this->command?->warn('Fichier SYSCOHADA JSON introuvable ou vide.');
            return;
        }

        $this->command?->info('Création du plan comptable SYSCOHADA de référence...');

        DB::transaction(function () use ($accounts) {
            // 1. Créer tous les comptes sans parent_id d'abord
            foreach ($accounts as $acc) {
                AccountingAccount::withoutEvents(function () use ($acc) {
                    AccountingAccount::updateOrCreate(
                        ['client_id' => 0, 'code' => $acc['code']],
                        [
                            'name'            => $acc['name'],
                            'type'            => $acc['type'] ?? 'equity',
                            'syscohada_class' => $acc['syscohada_class'],
                            'is_syscohada'    => true,
                            'is_active'       => true,
                            'tva_rate'        => $acc['tva_rate'] ?? null,
                            'has_tva'         => $acc['has_tva'] ?? false,
                        ]
                    );
                });
            }

            // 2. Définir les relations parent-enfant
            foreach ($accounts as $acc) {
                if (!empty($acc['parent_code'])) {
                    $parent = AccountingAccount::where('client_id', 0)
                        ->where('code', $acc['parent_code'])
                        ->first();
                    $child = AccountingAccount::where('client_id', 0)
                        ->where('code', $acc['code'])
                        ->first();

                    if ($parent && $child) {
                        $child->parent_id = $parent->id;
                        $child->saveQuietly();
                    }
                }
            }
        });

        $count = AccountingAccount::where('client_id', 0)->count();
        $this->command?->info("✓ {$count} comptes SYSCOHADA créés.");
    }

    /**
     * Duplique le plan comptable SYSCOHADA pour un client spécifique.
     * Utilisé lors de la création d'un nouveau client.
     */
    public static function createForClient(int $clientId): void
    {
        $accounts = AccountingAccount::where('client_id', 0)
            ->where('is_syscohada', true)
            ->orderBy('id')
            ->get();

        if ($accounts->isEmpty()) {
            // Fallback : charger depuis le JSON si la table est vide
            $seeder = new self();
            $seeder->run();
            $accounts = AccountingAccount::where('client_id', 0)
                ->where('is_syscohada', true)
                ->orderBy('id')
                ->get();
        }

        // Index des comptes de référence par code
        $refIndex = [];
        foreach ($accounts as $acc) {
            $refIndex[$acc->code] = $acc;
        }

        DB::transaction(function () use ($clientId, $accounts, $refIndex) {
            // Carte des IDs : refId => newId
            $idMap = [];

            // 1. Créer les comptes sans relations parent-enfant
            foreach ($accounts as $acc) {
                $new = AccountingAccount::firstOrCreate(
                    ['client_id' => $clientId, 'code' => $acc->code],
                    [
                        'name'            => $acc->name,
                        'type'            => $acc->type,
                        'syscohada_class' => $acc->syscohada_class,
                        'is_syscohada'    => true,
                        'is_active'       => true,
                        'tva_rate'        => $acc->tva_rate,
                        'has_tva'         => $acc->has_tva,
                    ]
                );
                $idMap[$acc->id] = $new->id;
            }

            // 2. Définir les relations parent-enfant
            foreach ($accounts as $acc) {
                if ($acc->parent_id && isset($idMap[$acc->parent_id])) {
                    $child = AccountingAccount::find($idMap[$acc->id]);
                    if ($child) {
                        $child->parent_id = $idMap[$acc->parent_id];
                        $child->saveQuietly();
                    }
                }
            }
        });
    }

    /**
     * Charge les comptes depuis le fichier JSON.
     */
    private function loadAccounts(): array
    {
        $path = base_path(self::JSON_PATH);
        if (!file_exists($path)) return [];

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }
}
