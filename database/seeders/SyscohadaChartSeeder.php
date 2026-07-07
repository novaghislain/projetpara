<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyscohadaChartSeeder extends Seeder
{
    private array $accounts;

    public function __construct()
    {
        $this->accounts = [
            // ════════════════════════════════════════════
            // CLASSE 1 : CAPITAUX (Comptes de capitaux)
            // ════════════════════════════════════════════
            ['code' => '101000', 'name' => 'Capital social',                     'type' => 'passive',    'class' => '1'],
            ['code' => '101100', 'name' => 'Capital individuel',                'type' => 'passive',    'class' => '1'],
            ['code' => '101200', 'name' => 'Capital société',                   'type' => 'passive',    'class' => '1'],
            ['code' => '101300', 'name' => 'Apporteurs',                        'type' => 'passive',    'class' => '1'],
            ['code' => '106000', 'name' => 'Réserves légales',                  'type' => 'passive',    'class' => '1'],
            ['code' => '106100', 'name' => 'Réserves statutaires',              'type' => 'passive',    'class' => '1'],
            ['code' => '106200', 'name' => 'Réserves facultatives',             'type' => 'passive',    'class' => '1'],
            ['code' => '106300', 'name' => 'Plus-values de réévaluation',       'type' => 'passive',    'class' => '1'],
            ['code' => '110000', 'name' => 'Report à nouveau (solde créditeur)','type' => 'passive',    'class' => '1'],
            ['code' => '119000', 'name' => 'Report à nouveau (solde débiteur)', 'type' => 'active',     'class' => '1'],
            ['code' => '120000', 'name' => "Résultat de l'exercice (bénéfice)", 'type' => 'passive',    'class' => '1'],
            ['code' => '129000', 'name' => "Résultat de l'exercice (perte)",    'type' => 'active',     'class' => '1'],
            ['code' => '131000', 'name' => "Subventions d'investissement",      'type' => 'passive',    'class' => '1'],
            ['code' => '141000', 'name' => 'Emprunts obligataires',             'type' => 'passive',    'class' => '1'],
            ['code' => '161000', 'name' => 'Emprunts bancaires',                'type' => 'passive',    'class' => '1'],
            ['code' => '164000', 'name' => 'Découverts bancaires',              'type' => 'passive',    'class' => '1'],
            ['code' => '166000', 'name' => 'Crédits de trésorerie',             'type' => 'passive',    'class' => '1'],
            ['code' => '168000', 'name' => 'Autres dettes financières',         'type' => 'passive',    'class' => '1'],

            // ════════════════════════════════════════════
            // CLASSE 2 : IMMOBILISATIONS
            // ════════════════════════════════════════════
            ['code' => '201000', 'name' => "Frais d'établissement",             'type' => 'active',     'class' => '2'],
            ['code' => '202000', 'name' => 'Frais de recherche et développement','type' => 'active',    'class' => '2'],
            ['code' => '203000', 'name' => 'Brevets, licences, marques',        'type' => 'active',     'class' => '2'],
            ['code' => '204000', 'name' => 'Fonds commercial',                  'type' => 'active',     'class' => '2'],
            ['code' => '206000', 'name' => 'Logiciels informatiques',           'type' => 'active',     'class' => '2'],
            ['code' => '211000', 'name' => 'Terrains agricoles',                'type' => 'active',     'class' => '2'],
            ['code' => '212000', 'name' => 'Terrains nus',                      'type' => 'active',     'class' => '2'],
            ['code' => '213000', 'name' => 'Constructions',                     'type' => 'active',     'class' => '2'],
            ['code' => '214000', 'name' => 'Agencements et aménagements',       'type' => 'active',     'class' => '2'],
            ['code' => '215000', 'name' => 'Matériel et outillage industriels', 'type' => 'active',     'class' => '2'],
            ['code' => '218100', 'name' => 'Matériel informatique',             'type' => 'active',     'class' => '2'],
            ['code' => '218200', 'name' => 'Mobilier de bureau',                'type' => 'active',     'class' => '2'],
            ['code' => '218300', 'name' => 'Matériel de transport',             'type' => 'active',     'class' => '2'],
            ['code' => '281000', 'name' => 'Amortissements frais établissement','type' => 'active',     'class' => '2'],
            ['code' => '282000', 'name' => 'Amortissements immobilisations',    'type' => 'active',     'class' => '2'],
            ['code' => '283000', 'name' => 'Amortissements constructions',      'type' => 'active',     'class' => '2'],
            ['code' => '284000', 'name' => 'Amortissements matériel et outillage','type' => 'active',   'class' => '2'],
            ['code' => '285000', 'name' => 'Amortissements matériel transport', 'type' => 'active',     'class' => '2'],
            ['code' => '286000', 'name' => 'Amortissements mobilier bureau',    'type' => 'active',     'class' => '2'],
            ['code' => '291000', 'name' => 'Provisions dépréciation immob.',    'type' => 'active',     'class' => '2'],

            // ════════════════════════════════════════════
            // CLASSE 3 : STOCKS
            // ════════════════════════════════════════════
            ['code' => '311000', 'name' => 'Marchandises',                      'type' => 'active',     'class' => '3'],
            ['code' => '312000', 'name' => 'Matières premières',                'type' => 'active',     'class' => '3'],
            ['code' => '315000', 'name' => 'Produits finis',                    'type' => 'active',     'class' => '3'],
            ['code' => '391000', 'name' => 'Provisions dépréciation stocks',    'type' => 'active',     'class' => '3'],

            // ════════════════════════════════════════════
            // CLASSE 4 : TIERS
            // ════════════════════════════════════════════
            ['code' => '401000', 'name' => 'Fournisseurs',                      'type' => 'passive',    'class' => '4'],
            ['code' => '401100', 'name' => 'Fournisseurs locaux',               'type' => 'passive',    'class' => '4'],
            ['code' => '401200', 'name' => 'Fournisseurs étrangers',            'type' => 'passive',    'class' => '4'],
            ['code' => '404000', 'name' => "Fournisseurs d'immobilisations",    'type' => 'passive',    'class' => '4'],
            ['code' => '408000', 'name' => 'Fournisseurs factures non parvenues','type' => 'passive',   'class' => '4'],
            ['code' => '409000', 'name' => 'Avances fournisseurs',              'type' => 'active',     'class' => '4'],
            ['code' => '411000', 'name' => 'Clients',                           'type' => 'active',     'class' => '4'],
            ['code' => '411100', 'name' => 'Clients locaux',                    'type' => 'active',     'class' => '4'],
            ['code' => '411200', 'name' => 'Clients étrangers',                 'type' => 'active',     'class' => '4'],
            ['code' => '416000', 'name' => 'Créances litigieuses',              'type' => 'active',     'class' => '4'],
            ['code' => '418000', 'name' => 'Clients produits non facturés',     'type' => 'active',     'class' => '4'],
            ['code' => '419000', 'name' => 'Avances clients',                   'type' => 'passive',    'class' => '4'],
            ['code' => '421000', 'name' => 'Personnel rémunérations dues',      'type' => 'passive',    'class' => '4'],
            ['code' => '423000', 'name' => 'Organismes sociaux',                'type' => 'passive',    'class' => '4'],
            ['code' => '431000', 'name' => "État impôts et taxes",              'type' => 'passive',    'class' => '4'],
            ['code' => '431100', 'name' => 'État TVA collectée',                'type' => 'passive',    'class' => '4'],
            ['code' => '431200', 'name' => 'État TVA récupérable',              'type' => 'active',     'class' => '4'],
            ['code' => '431300', 'name' => 'État impôt sur bénéfices',          'type' => 'passive',    'class' => '4'],
            ['code' => '432000', 'name' => 'Autres impôts et taxes',            'type' => 'passive',    'class' => '4'],
            ['code' => '441000', 'name' => 'Actionnaires ou associés',          'type' => 'passive',    'class' => '4'],
            ['code' => '444000', 'name' => 'Dettes sur acquisitions',           'type' => 'passive',    'class' => '4'],
            ['code' => '471000', 'name' => "Comptes d'attente",                 'type' => 'active',     'class' => '4'],
            ['code' => '472000', 'name' => 'Charges constatées d\'avance',      'type' => 'active',     'class' => '4'],
            ['code' => '478000', 'name' => 'Produits constatés d\'avance',      'type' => 'passive',    'class' => '4'],
            ['code' => '481000', 'name' => 'Créances sur l\'État',              'type' => 'active',     'class' => '4'],

            // ════════════════════════════════════════════
            // CLASSE 5 : TRÉSORERIE
            // ════════════════════════════════════════════
            ['code' => '511000', 'name' => 'Banques locales',                   'type' => 'active',     'class' => '5'],
            ['code' => '511100', 'name' => 'Société Générale',                  'type' => 'active',     'class' => '5'],
            ['code' => '511200', 'name' => 'Ecobank',                           'type' => 'active',     'class' => '5'],
            ['code' => '511300', 'name' => 'BOA',                               'type' => 'active',     'class' => '5'],
            ['code' => '512000', 'name' => 'Banques étrangères',                'type' => 'active',     'class' => '5'],
            ['code' => '521000', 'name' => 'Caisse',                            'type' => 'active',     'class' => '5'],
            ['code' => '521100', 'name' => 'Caisse principale',                 'type' => 'active',     'class' => '5'],
            ['code' => '521200', 'name' => 'Caisse auxiliaire',                 'type' => 'active',     'class' => '5'],
            ['code' => '531000', 'name' => 'Règlements par carte',              'type' => 'active',     'class' => '5'],
            ['code' => '541000', 'name' => 'Chèques à encaisser',               'type' => 'active',     'class' => '5'],
            ['code' => '581000', 'name' => 'Virements internes',                'type' => 'active',     'class' => '5'],

            // ════════════════════════════════════════════
            // CLASSE 6 : CHARGES
            // ════════════════════════════════════════════
            ['code' => '601000', 'name' => 'Achats de marchandises',            'type' => 'charge',     'class' => '6'],
            ['code' => '602000', 'name' => 'Achats de matières premières',      'type' => 'charge',     'class' => '6'],
            ['code' => '603000', 'name' => 'Achats de fournitures',             'type' => 'charge',     'class' => '6'],
            ['code' => '604000', 'name' => 'Variations des stocks',             'type' => 'charge',     'class' => '6'],
            ['code' => '611000', 'name' => 'Transports',                        'type' => 'charge',     'class' => '6'],
            ['code' => '612000', 'name' => 'Location',                          'type' => 'charge',     'class' => '6'],
            ['code' => '613000', 'name' => 'Entretien et réparations',          'type' => 'charge',     'class' => '6'],
            ['code' => '614000', 'name' => 'Assurances',                        'type' => 'charge',     'class' => '6'],
            ['code' => '615000', 'name' => 'Documentation',                     'type' => 'charge',     'class' => '6'],
            ['code' => '616000', 'name' => 'Personnel extérieur',               'type' => 'charge',     'class' => '6'],
            ['code' => '621000', 'name' => 'Redevances crédit-bail',            'type' => 'charge',     'class' => '6'],
            ['code' => '631000', 'name' => 'Frais bancaires',                   'type' => 'charge',     'class' => '6'],
            ['code' => '632000', 'name' => 'Commissions bancaires',             'type' => 'charge',     'class' => '6'],
            ['code' => '641000', 'name' => 'Salaires appointements',            'type' => 'charge',     'class' => '6'],
            ['code' => '642000', 'name' => 'Charges sociales',                  'type' => 'charge',     'class' => '6'],
            ['code' => '643000', 'name' => 'Indemnités',                        'type' => 'charge',     'class' => '6'],
            ['code' => '651000', 'name' => 'Impôts et taxes',                   'type' => 'charge',     'class' => '6'],
            ['code' => '652000', 'name' => 'Taxe sur valeur ajoutée',           'type' => 'charge',     'class' => '6'],
            ['code' => '661000', 'name' => 'Intérêts des emprunts',             'type' => 'charge',     'class' => '6'],
            ['code' => '671000', 'name' => 'Charges exceptionnelles',           'type' => 'charge',     'class' => '6'],
            ['code' => '681000', 'name' => 'Dotations aux amortissements',      'type' => 'charge',     'class' => '6'],
            ['code' => '691000', 'name' => 'Dotations aux provisions',          'type' => 'charge',     'class' => '6'],

            // ════════════════════════════════════════════
            // CLASSE 7 : PRODUITS
            // ════════════════════════════════════════════
            ['code' => '701000', 'name' => 'Ventes de marchandises',            'type' => 'produit',    'class' => '7'],
            ['code' => '702000', 'name' => 'Ventes de produits finis',          'type' => 'produit',    'class' => '7'],
            ['code' => '711000', 'name' => 'Prestations de services',           'type' => 'produit',    'class' => '7'],
            ['code' => '712000', 'name' => 'Commissions',                       'type' => 'produit',    'class' => '7'],
            ['code' => '721000', 'name' => 'Produits accessoires',              'type' => 'produit',    'class' => '7'],
            ['code' => '741000', 'name' => "Subventions d'exploitation",        'type' => 'produit',    'class' => '7'],
            ['code' => '751000', 'name' => 'Produits financiers',              'type' => 'produit',    'class' => '7'],
            ['code' => '752000', 'name' => 'Intérêts',                          'type' => 'produit',    'class' => '7'],
            ['code' => '761000', 'name' => 'Produits exceptionnels',            'type' => 'produit',    'class' => '7'],
            ['code' => '771000', 'name' => 'Reprises sur amortissements',       'type' => 'produit',    'class' => '7'],
            ['code' => '781000', 'name' => 'Reprises sur provisions',           'type' => 'produit',    'class' => '7'],

            // ════════════════════════════════════════════
            // CLASSE 8 : COMPTES SPÉCIAUX
            // ════════════════════════════════════════════
            ['code' => '801000', 'name' => 'Engagements donnés',                'type' => 'passive',    'class' => '8'],
            ['code' => '802000', 'name' => 'Engagements reçus',                 'type' => 'passive',    'class' => '8'],
            ['code' => '890000', 'name' => "Bilan d'ouverture",                'type' => 'passive',    'class' => '8'],
            ['code' => '891000', 'name' => "Bilan de clôture",                 'type' => 'passive',    'class' => '8'],
        ];
    }

    /**
     * Seed le plan comptable SYSCOHADA de référence (client_id = 0).
     * Ces comptes sont utilisés comme modèle pour créer les plans des clients.
     */
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->accounts as $acc) {
                AccountingAccount::withoutEvents(function () use ($acc) {
                    AccountingAccount::updateOrCreate(
                        ['client_id' => 0, 'code' => $acc['code']],
                        [
                            'name'            => $acc['name'],
                            'type'            => $acc['type'],
                            'syscohada_class' => $acc['class'],
                            'is_syscohada'    => true,
                            'is_active'       => true,
                        ]
                    );
                });
            }
        });

        $count = AccountingAccount::where('client_id', 0)->count();
        $this->command?->info("✓ {$count} comptes SYSCOHADA créés (client_id = 0).");
    }

    /**
     * Duplique le plan comptable SYSCOHADA pour un client spécifique.
     */
    public static function createForClient(int $clientId): void
    {
        $accounts = AccountingAccount::where('client_id', 0)
            ->where('is_syscohada', true)
            ->orderBy('id')
            ->get();

        if ($accounts->isEmpty()) {
            $seeder = app(self::class);
            $seeder->run();
            $accounts = AccountingAccount::where('client_id', 0)
                ->where('is_syscohada', true)
                ->orderBy('id')
                ->get();
        }

        DB::transaction(function () use ($clientId, $accounts) {
            $idMap = [];

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

    public function getAccounts(): array
    {
        return $this->accounts;
    }
}
