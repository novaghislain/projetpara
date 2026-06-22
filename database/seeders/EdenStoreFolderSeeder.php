<?php

namespace Database\Seeders;

use App\Models\ClientFolder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EdenStoreFolderSeeder extends Seeder
{
    /**
     * Structure Eden Store : 4 catégories racines avec arborescence complète.
     * Chaque client reçoit cette hiérarchie à sa création.
     */
    private array $edenStructure = [
        [
            'name' => 'Administratif',
            'slug' => 'administratif',
            'sort_order' => 1,
            'is_system' => true,
            'children' => [
                [
                    'name' => 'Registre de Commerce (RCCM)',
                    'slug' => 'rccm',
                    'children' => [
                        ['name' => 'Immatriculation', 'slug' => 'immatriculation'],
                        ['name' => 'Modifications statutaires', 'slug' => 'modifications-statutaires'],
                        ['name' => 'Certificats de non-poursuite', 'slug' => 'certificats-non-poursuite'],
                    ],
                ],
                [
                    'name' => 'Statuts & Assemblées',
                    'slug' => 'statuts-assemblees',
                    'children' => [
                        ['name' => 'Statuts constitutifs', 'slug' => 'statuts-constitutifs'],
                        ['name' => 'Procès-verbaux AG', 'slug' => 'proces-verbaux-ag'],
                        ['name' => 'Pouvoirs & délégations', 'slug' => 'pouvoirs-delegations'],
                        ['name' => 'Registre des décisions', 'slug' => 'registre-decisions'],
                    ],
                ],
                [
                    'name' => 'Identifiant Fiscal (IFU)',
                    'slug' => 'ifu',
                    'children' => [
                        ['name' => 'Carte IFU', 'slug' => 'carte-ifu'],
                        ['name' => 'Attestations fiscales', 'slug' => 'attestations-fiscales'],
                    ],
                ],
                [
                    'name' => 'CNSS',
                    'slug' => 'cnss',
                    'children' => [
                        ['name' => 'Immatriculation employeur', 'slug' => 'immatriculation-employeur'],
                        ['name' => 'Déclarations mensuelles', 'slug' => 'declarations-mensuelles'],
                        ['name' => 'Attestations de régularité', 'slug' => 'attestations-regularite'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Courant / Annuel',
            'slug' => 'courant-annuel',
            'sort_order' => 2,
            'is_system' => true,
            'children' => [
                [
                    'name' => 'Comptabilité',
                    'slug' => 'comptabilite',
                    'children' => [
                        ['name' => 'Bilans annuels', 'slug' => 'bilans-annuels'],
                        ['name' => 'Grands livres', 'slug' => 'grands-livres'],
                        ['name' => 'Balances', 'slug' => 'balances'],
                        ['name' => 'Journaux', 'slug' => 'journaux'],
                        ['name' => 'Pièces comptables', 'slug' => 'pieces-comptables'],
                        ['name' => 'Factures fournisseurs', 'slug' => 'factures-fournisseurs'],
                        ['name' => 'Factures clients', 'slug' => 'factures-clients'],
                    ],
                ],
                [
                    'name' => 'Fiscal',
                    'slug' => 'fiscal',
                    'children' => [
                        ['name' => 'Déclarations TVA', 'slug' => 'declarations-tva'],
                        ['name' => 'Déclarations IR/IS', 'slug' => 'declarations-ir-is'],
                        ['name' => 'Déclarations IRCM', 'slug' => 'declarations-ircm'],
                        ['name' => 'Avis d\'imposition', 'slug' => 'avis-imposition'],
                    ],
                ],
                [
                    'name' => 'Social',
                    'slug' => 'social',
                    'children' => [
                        ['name' => 'Contrats de travail', 'slug' => 'contrats-travail'],
                        ['name' => 'Fiches de paie', 'slug' => 'fiches-paie'],
                        ['name' => 'Déclarations CNSS', 'slug' => 'declarations-cnss'],
                        ['name' => 'Registre du personnel', 'slug' => 'registre-personnel'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Permanent',
            'slug' => 'permanent',
            'sort_order' => 3,
            'is_system' => true,
            'children' => [
                [
                    'name' => 'Documents Juridiques',
                    'slug' => 'documents-juridiques',
                    'children' => [
                        ['name' => 'Contrats commerciaux', 'slug' => 'contrats-commerciaux'],
                        ['name' => 'Baux & locations', 'slug' => 'baux-locations'],
                        ['name' => 'Conventions', 'slug' => 'conventions'],
                        ['name' => 'Marchés publics', 'slug' => 'marches-publics'],
                    ],
                ],
                [
                    'name' => 'Propriété Intellectuelle',
                    'slug' => 'propriete-intellectuelle',
                    'children' => [
                        ['name' => 'Marques & brevets', 'slug' => 'marques-brevets'],
                        ['name' => 'Licences', 'slug' => 'licences'],
                    ],
                ],
                [
                    'name' => 'Assurances',
                    'slug' => 'assurances',
                    'children' => [
                        ['name' => 'Polices d\'assurance', 'slug' => 'polices-assurance'],
                        ['name' => 'Sinistres', 'slug' => 'sinistres'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Spécial / Ponctuel',
            'slug' => 'special-ponctuel',
            'sort_order' => 4,
            'is_system' => true,
            'children' => [
                [
                    'name' => 'Contentieux',
                    'slug' => 'contentieux',
                    'children' => [
                        ['name' => 'Correspondances', 'slug' => 'correspondances'],
                        ['name' => 'Actes de procédure', 'slug' => 'actes-procedure'],
                        ['name' => 'Jugements & arrêts', 'slug' => 'jugements-arrets'],
                    ],
                ],
                [
                    'name' => 'Projets Spéciaux',
                    'slug' => 'projets-speciaux',
                    'children' => [
                        ['name' => 'Études & rapports', 'slug' => 'etudes-rapports'],
                        ['name' => 'Correspondances diverses', 'slug' => 'correspondances-diverses'],
                        ['name' => 'Documentation technique', 'slug' => 'documentation-technique'],
                    ],
                ],
                [
                    'name' => 'Archives',
                    'slug' => 'archives',
                    'children' => [
                        ['name' => 'Exercices antérieurs', 'slug' => 'exercices-anterieurs'],
                        ['name' => 'Documents obsolètes', 'slug' => 'documents-obsoletes'],
                    ],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        // Ce seeder crée la structure pour TOUS les clients existants
        $clients = \App\Models\Client::all();

        foreach ($clients as $client) {
            $this->createStructureForClient($client->id);
        }

        $this->command->info('✅ Hiérarchie Eden Store créée pour ' . $clients->count() . ' client(s).');
    }

    /**
     * Crée la hiérarchie Eden Store pour un client spécifique.
     */
    public function createStructureForClient(int $clientId): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SET app.client_id = '{$clientId}'");
        }
        try {
            foreach ($this->edenStructure as $rootData) {
                $root = $this->createFolder($clientId, null, $rootData, 1, '');
                if (!empty($rootData['children'])) {
                    $this->createChildren($clientId, $root->id, $rootData['children'], 2, $root->slug);
                }
            }
        } finally {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement("SET app.client_id = '0'");
            }
        }
    }

    private function createChildren(int $clientId, ?int $parentId, array $children, int $level, string $parentPath): void
    {
        foreach ($children as $childData) {
            $folder = $this->createFolder($clientId, $parentId, $childData, $level, $parentPath);
            if (!empty($childData['children'])) {
                $this->createChildren($clientId, $folder->id, $childData['children'], $level + 1, $folder->slug);
            }
        }
    }

    private function createFolder(int $clientId, ?int $parentId, array $data, int $level, string $parentPath): ClientFolder
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $path = $parentPath ? "{$parentPath}/{$slug}" : $slug;

        return ClientFolder::firstOrCreate(
            [
                'client_id' => $clientId,
                'slug' => $slug,
                'parent_id' => $parentId,
            ],
            [
                'name' => $data['name'],
                'path' => $path,
                'level' => $level,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_system' => $data['is_system'] ?? false,
            ]
        );
    }
}
