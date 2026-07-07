<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Ajoute les comptes de regroupement SYSCOHADA manquants pour former
 * une arborescence complète (classes 1-9 + sous-groupes).
 *
 * Exemple : 1 → 10 → 101 → 1011
 *           (manquants: 1, 10)
 */
class SyscohadaTreeSeeder extends Seeder
{
    /**
     * Comptes de regroupement à créer si absents.
     * [code, name, class, type, nature, parent_code]
     */
    protected array $groups = [
        // ── Classe 1 : Capitaux permanents ──
        ['1',  'CAPITAUX PERMANENTS',                    '1', 'equity',    'creditor',  null, 'Capitaux propres et ressources durables'],
        ['10', 'Capital et réserves',                     '1', 'equity',    'creditor',  '1',   'Capital social, réserves et report à nouveau'],
        ['11', 'Subventions et contributions',            '1', 'equity',    'creditor',  '1',   'Aides et apports reçus'],
        ['12', 'Dettes financières',                      '1', 'liability', 'creditor',  '1',   'Emprunts et dettes assimilées'],
        ['13', 'Provisions pour risques et charges',      '1', 'liability', 'creditor',  '1',   'Provisions réglementées'],

        // ── Classe 2 : Actif immobilisé ──
        ['2',  'ACTIF IMMOBILISÉ',                        '2', 'asset',     'debitor',   null, 'Valeurs immobilisées'],
        ['20', 'Charges immobilisées',                    '2', 'asset',     'debitor',   '2',   'Frais d\'établissement et R&D'],
        ['21', 'Immobilisations incorporelles',           '2', 'asset',     'debitor',   '2',   'Brevets, licences, logiciels'],
        ['22', 'Immobilisations corporelles',             '2', 'asset',     'debitor',   '2',   'Terrains, bâtiments, matériels'],
        ['23', 'Immobilisations en cours',                '2', 'asset',     'debitor',   '2',   'Immos non achevées'],
        ['24', 'Immobilisations financières',             '2', 'asset',     'debitor',   '2',   'Titres, prêts, dépôts'],
        ['25', 'Amortissements',                          '2', 'contra_asset', 'creditor', '2', 'Amortissements cumulés'],
        ['26', 'Dépréciations',                           '2', 'contra_asset', 'creditor', '2', 'Dépréciations des immobilisations'],

        // ── Classe 3 : Actif circulant ──
        ['3',  'ACTIF CIRCULANT',                         '3', 'asset',     'debitor',   null, 'Valeurs d\'exploitation et disponibilités'],
        ['30', 'Stocks',                                  '3', 'asset',     'debitor',   '3',   'Marchandises, matières, produits'],
        ['31', 'Créances',                                '3', 'asset',     'debitor',   '3',   'Créances clients et autres'],
        ['32', 'Trésorerie',                              '3', 'asset',     'debitor',   '3',   'Banques, caisse, CCP'],

        // ── Classe 4 : Passif circulant ──
        ['4',  'PASSIF CIRCULANT',                        '4', 'liability', 'creditor',  null, 'Dettes à court terme'],
        ['40', 'Dettes fournisseurs',                     '4', 'liability', 'creditor',  '4',   'Fournisseurs et comptes rattachés'],
        ['42', 'Dettes fiscales et sociales',             '4', 'liability', 'creditor',  '4',   'État, organismes sociaux, personnel'],
        ['44', 'Autres dettes',                           '4', 'liability', 'creditor',  '4',   'Dettes diverses'],

        // ── Classe 5 : Comptes de gestion ──
        ['5',  'COMPTES DE GESTION',                      '5', 'expense',   'debitor',   null, 'Comptes de gestion des stocks'],

        // ── Classe 6 : Charges ──
        ['6',  'CHARGES',                                 '6', 'expense',   'debitor',   null, 'Charges par nature'],
        ['60', 'Achats et variations de stocks',          '6', 'expense',   'debitor',   '6',   'Matières et marchandises'],
        ['61', 'Services extérieurs',                     '6', 'expense',   'debitor',   '6',   'Loyers, entretien, transports'],
        ['62', 'Charges de personnel',                    '6', 'expense',   'debitor',   '6',   'Salaires, charges sociales'],
        ['63', 'Impôts et taxes',                         '6', 'expense',   'debitor',   '6',   'Impôts directs et indirects'],
        ['64', 'Charges financières',                     '6', 'expense',   'debitor',   '6',   'Intérêts et charges assimilées'],
        ['65', 'Dotations aux amortissements',            '6', 'expense',   'debitor',   '6',   'Dotations et provisions'],
        ['68', 'Charges exceptionnelles',                 '6', 'expense',   'debitor',   '6',   'Charges hors exploitation'],

        // ── Classe 7 : Produits ──
        ['7',  'PRODUITS',                                '7', 'revenue',   'creditor',  null, 'Produits par nature'],
        ['70', 'Ventes et produits d\'exploitation',      '7', 'revenue',   'creditor',  '7',   'Ventes de biens et services'],
        ['71', 'Produits financiers',                     '7', 'revenue',   'creditor',  '7',   'Revenus financiers'],
        ['72', 'Produits exceptionnels',                  '7', 'revenue',   'creditor',  '7',   'Produits hors exploitation'],
        ['73', 'Reprises et transferts',                  '7', 'revenue',   'creditor',  '7',   'Reprises sur provisions'],

        // ── Classe 8 : Comptes spéciaux ──
        ['8',  'COMPTES SPÉCIAUX',                         '8', 'special',   'bilateral', null, 'Engagements et affectations'],

        // ── Classe 9 : Comptes analytiques ──
        ['9',  'COMPTES ANALYTIQUES',                      '9', 'analytical','bilateral', null, 'Comptabilité analytique'],
    ];

    public function run(): void
    {
        $this->command?->info('Ajout des comptes de regroupement SYSCOHADA...');

        $existing = AccountingAccount::where('client_id', 0)
            ->where('is_syscohada', true)
            ->get()
            ->keyBy('code');

        $added = 0;
        DB::transaction(function () use ($existing, &$added) {
            $newAccounts = [];

            foreach ($this->groups as [$code, $name, $class, $type, $nature, $parentCode, $description]) {
                if ($existing->has($code)) {
                    // Mettre à jour les métadonnées si déjà existant
                    $acc = $existing->get($code);
                    $acc->updateQuietly([
                        'is_summary' => true,
                        'allow_journal_entry' => false,
                        'account_nature' => $nature,
                        'description' => $description,
                    ]);
                    $newAccounts[$code] = $acc;
                    continue;
                }

                $parentId = null;
                if ($parentCode && isset($newAccounts[$parentCode])) {
                    $parentId = $newAccounts[$parentCode]->id;
                } elseif ($parentCode && $existing->has($parentCode)) {
                    $parentId = $existing->get($parentCode)->id;
                }

                $acc = AccountingAccount::create([
                    'client_id'          => 0,
                    'code'               => $code,
                    'name'               => $name,
                    'type'               => $type,
                    'syscohada_class'    => $class,
                    'account_nature'     => $nature,
                    'is_syscohada'       => true,
                    'is_active'          => true,
                    'is_summary'         => true,
                    'allow_journal_entry'=> false,
                    'parent_id'          => $parentId,
                ]);

                $newAccounts[$code] = $acc;
                $added++;
            }
        });

        $total = AccountingAccount::where('client_id', 0)->where('is_syscohada', true)->count();
        $this->command?->info("✓ {$added} comptes de regroupement créés. Total: {$total} comptes SYSCOHADA.");
    }
}
