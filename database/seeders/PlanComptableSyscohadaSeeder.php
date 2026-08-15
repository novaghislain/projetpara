<?php

namespace Database\Seeders;

use App\Models\Gel\Cabinet;
use App\Models\Gel\Comptabilite\CompteComptable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanComptableSyscohadaSeeder extends Seeder
{
    /**
     * Plan comptable SYSCOHADA révisé — OHADA
     * Tel qu'applicable au Bénin et dans l'espace OHADA.
     *
     * Structure du code SYSCOHADA : X-Y-ZZ-WW (ex: 4-0-1-1-00 = Fournisseurs)
     * Simplification ici : code sur 10 caractères max avec tirets.
     */
    private array $comptes = [
        // ═══════════════════ CLASSE 1 — CAPITAUX ═══════════════════
        ['code' => '101',     'intitule' => 'Capital social',                     'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '1011',    'intitule' => 'Capital appelé non versé',          'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '1012',    'intitule' => 'Capital appelé versé',              'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '104',     'intitule' => 'Primes d\'apport',                  'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '105',     'intitule' => 'Primes de fusion',                  'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '106',     'intitule' => 'Réserves légales',                  'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '1061',    'intitule' => 'Réserves statutaires',              'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '1063',    'intitule' => 'Réserves réglementées',             'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '1068',    'intitule' => 'Autres réserves',                   'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '107',     'intitule' => 'Report à nouveau (solde créditeur)','classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '1071',    'intitule' => 'Report à nouveau (solde débiteur)', 'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '108',     'intitule' => 'Résultat net de l\'exercice',       'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '109',     'intitule' => 'Résultat en instance d\'affectation','classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '11',      'intitule' => 'Primes et réserves',                'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '12',      'intitule' => 'Report à nouveau',                  'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '13',      'intitule' => 'Résultat net',                      'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '131',     'intitule' => 'Prêts et créances assimilées',      'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '132',     'intitule' => 'Avances et acomptes',               'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '14',      'intitule' => 'Subventions d\'investissement',     'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '15',      'intitule' => 'Provisions réglementées',           'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '16',      'intitule' => 'Emprunts et dettes assimilées',     'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '161',     'intitule' => 'Emprunts obligataires',             'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '162',     'intitule' => 'Emprunts bancaires',                'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '163',     'intitule' => 'Dettes de crédit-bail',             'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '164',     'intitule' => 'Dettes financières diverses',       'classe' => '1', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '17',      'intitule' => 'Dettes de location-acquisition',    'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '18',      'intitule' => 'Comptes de liaison',                'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '19',      'intitule' => 'Provisions pour risques',           'classe' => '1', 'niveau' => 2, 'solde_debiteur' => false],

        // ═══════════════════ CLASSE 2 — IMMOBILISATIONS ═══════════════════
        ['code' => '201',     'intitule' => 'Frais de recherche et développement','classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '202',     'intitule' => 'Frais d\'établissement',            'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '203',     'intitule' => 'Frais d\'expansion',                'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '204',     'intitule' => 'Logiciels et progiciels',           'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '205',     'intitule' => 'Brevets, licences et marques',      'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '206',     'intitule' => 'Droit au bail',                     'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '207',     'intitule' => 'Fonds commercial',                  'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '21',      'intitule' => 'Terrains',                          'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '211',     'intitule' => 'Terrains nus',                      'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '212',     'intitule' => 'Terrains bâtis',                    'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '213',     'intitule' => 'Terrains aménagés',                 'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '22',      'intitule' => 'Bâtiments / Constructions',         'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '221',     'intitule' => 'Constructions sur sol propre',      'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '222',     'intitule' => 'Constructions sur sol d\'autrui',   'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '23',      'intitule' => 'Installations techniques',          'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '24',      'intitule' => 'Matériel de transport',             'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '241',     'intitule' => 'Véhicules utilitaires',             'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '242',     'intitule' => 'Véhicules de tourisme',             'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '25',      'intitule' => 'Mobilier et matériel de bureau',    'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '251',     'intitule' => 'Mobilier de bureau',                'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '252',     'intitule' => 'Matériel informatique',             'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '253',     'intitule' => 'Matériel de bureau',                'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '26',      'intitule' => 'Autres immobilisations corporelles','classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '27',      'intitule' => 'Immobilisations en cours',          'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '28',      'intitule' => 'Amortissements',                    'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '281',     'intitule' => 'Amortissements des immobilisations incorporelles', 'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '282',     'intitule' => 'Amortissements des constructions',  'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '284',     'intitule' => 'Amortissements du matériel de transport', 'classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '285',     'intitule' => 'Amortissements du mobilier et matériel','classe' => '2', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '29',      'intitule' => 'Provisions pour dépréciation des immobilisations', 'classe' => '2', 'niveau' => 2, 'solde_debiteur' => true],

        // ═══════════════════ CLASSE 3 — STOCKS ═══════════════════
        ['code' => '31',      'intitule' => 'Marchandises',                      'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '311',     'intitule' => 'Marchandises A',                    'classe' => '3', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '312',     'intitule' => 'Marchandises B',                    'classe' => '3', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '32',      'intitule' => 'Matières premières',               'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '33',      'intitule' => 'Autres approvisionnements',         'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '34',      'intitule' => 'Produits en cours',                 'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '35',      'intitule' => 'Produits finis',                    'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '351',     'intitule' => 'Produits finis A',                  'classe' => '3', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '36',      'intitule' => 'Stocks en cours de route',          'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '37',      'intitule' => 'Marchandises en consignation',      'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '38',      'intitule' => 'Stocks dans un tiers',              'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '39',      'intitule' => 'Provisions pour dépréciation des stocks', 'classe' => '3', 'niveau' => 2, 'solde_debiteur' => true],

        // ═══════════════════ CLASSE 4 — TIERS ═══════════════════
        ['code' => '401',     'intitule' => 'Fournisseurs',                      'classe' => '4', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '4011',    'intitule' => 'Fournisseurs locaux',               'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '4012',    'intitule' => 'Fournisseurs étrangers',            'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '404',     'intitule' => 'Fournisseurs d\'immobilisations',   'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '408',     'intitule' => 'Fournisseurs — factures non parvenues', 'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '409',     'intitule' => 'Fournisseurs débiteurs / avances',  'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '41',      'intitule' => 'Clients',                           'classe' => '4', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '411',     'intitule' => 'Clients locaux',                    'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '412',     'intitule' => 'Clients étrangers',                 'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '413',     'intitule' => 'Clients, groupe',                   'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '414',     'intitule' => 'Clients, effets à recevoir',        'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '415',     'intitule' => 'Clients douteux ou litigieux',      'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '416',     'intitule' => 'Clients, avances et acomptes',      'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '418',     'intitule' => 'Clients — factures à établir',      'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '419',     'intitule' => 'Clients créditeurs',                'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '42',      'intitule' => 'Personnel',                         'classe' => '4', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '421',     'intitule' => 'Personnel — rémunérations dues',    'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '422',     'intitule' => 'Personnel — avances et acomptes',   'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '423',     'intitule' => 'Personnel — oppositions',           'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '43',      'intitule' => 'Organismes sociaux',                'classe' => '4', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '431',     'intitule' => 'Sécurité sociale',                  'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '432',     'intitule' => 'Autres organismes sociaux',         'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '44',      'intitule' => 'État et collectivités publiques',   'classe' => '4', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '441',     'intitule' => 'État — Impôts sur les bénéfices',   'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '442',     'intitule' => 'État — Autres impôts et taxes',     'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '443',     'intitule' => 'État — TVA facturée',               'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '444',     'intitule' => 'État — TVA due ou crédit de TVA',   'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '445',     'intitule' => 'État — TVA récupérable',            'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '446',     'intitule' => 'État — Impôts et taxes sur rémunérations', 'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '447',     'intitule' => 'État — Impôts et taxes retenus à la source', 'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '448',     'intitule' => 'État — Charges à payer et produits à recevoir', 'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],

        ['code' => '45',      'intitule' => 'Débiteurs et créditeurs divers',    'classe' => '4', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '451',     'intitule' => 'Débiteurs divers',                  'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '452',     'intitule' => 'Créditeurs divers',                 'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '453',     'intitule' => 'Créances sur cessions d\'immobilisations', 'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '455',     'intitule' => 'Comptes transitoires — débiteurs',  'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '456',     'intitule' => 'Comptes transitoires — créditeurs', 'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '458',     'intitule' => 'Charges constatées d\'avance',      'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '459',     'intitule' => 'Produits constatés d\'avance',      'classe' => '4', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '47',      'intitule' => 'Comptes d\'attente et de régularisation', 'classe' => '4', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '471',     'intitule' => 'Comptes d\'attente',                'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '472',     'intitule' => 'Charges à répartir',                'classe' => '4', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '48',      'intitule' => 'Provisions pour dépréciation des comptes de tiers', 'classe' => '4', 'niveau' => 2, 'solde_debiteur' => true],

        // ═══════════════════ CLASSE 5 — TRÉSORERIE ═══════════════════
        ['code' => '501',     'intitule' => 'Banque',                            'classe' => '5', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '5011',    'intitule' => 'Banque locale',                     'classe' => '5', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '5012',    'intitule' => 'Banque étrangère',                  'classe' => '5', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '502',     'intitule' => 'Comptes chèques postaux',           'classe' => '5', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '503',     'intitule' => 'Caisse d\'épargne',                 'classe' => '5', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '51',      'intitule' => 'Caisse',                            'classe' => '5', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '511',     'intitule' => 'Caisse principale',                 'classe' => '5', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '512',     'intitule' => 'Caisse auxiliaire',                 'classe' => '5', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '52',      'intitule' => 'Virements internes',                'classe' => '5', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '53',      'intitule' => 'Titres de placement',               'classe' => '5', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '54',      'intitule' => 'Intérêts courus',                   'classe' => '5', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '55',      'intitule' => 'Provisions pour dépréciation des comptes de trésorerie', 'classe' => '5', 'niveau' => 2, 'solde_debiteur' => true],

        // ═══════════════════ CLASSE 6 — CHARGES ═══════════════════
        ['code' => '601',     'intitule' => 'Achats de marchandises',            'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '6011',    'intitule' => 'Achats de marchandises — locaux',   'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '6012',    'intitule' => 'Achats de marchandises — importation','classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '602',     'intitule' => 'Achats de matières premières',      'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '603',     'intitule' => 'Achats de fournitures',             'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '604',     'intitule' => 'Achats d\'emballages',              'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '605',     'intitule' => 'Achats de petit outillage',         'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '608',     'intitule' => 'Achats de bureau et fournitures',   'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '609',     'intitule' => 'Rabais, remises et ristournes obtenus', 'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '61',      'intitule' => 'Transports',                        'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '611',     'intitule' => 'Transports sur achats',             'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '612',     'intitule' => 'Transports sur ventes',             'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '613',     'intitule' => 'Transports pour le compte de tiers','classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '62',      'intitule' => 'Services extérieurs',               'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '621',     'intitule' => 'Locations et charges locatives',    'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '622',     'intitule' => 'Entretien et réparations',          'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '623',     'intitule' => 'Primes d\'assurances',              'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '624',     'intitule' => 'Honoraires et commissions',         'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '625',     'intitule' => 'Publicité et relations publiques',  'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '626',     'intitule' => 'Frais de télécommunications',       'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '627',     'intitule' => 'Frais bancaires',                   'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '628',     'intitule' => 'Autres services extérieurs',        'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '63',      'intitule' => 'Impôts et taxes',                   'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '631',     'intitule' => 'Impôts directs',                    'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '632',     'intitule' => 'Impôts indirects',                  'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '633',     'intitule' => 'Taxes sur le chiffre d\'affaires',  'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '634',     'intitule' => 'Droits d\'enregistrement',          'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '635',     'intitule' => 'Pénalités et amendes fiscales',     'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '64',      'intitule' => 'Frais de personnel',                'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '641',     'intitule' => 'Salaires et traitements',           'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '644',     'intitule' => 'Charges sociales',                  'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '645',     'intitule' => 'Autres charges de personnel',       'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '65',      'intitule' => 'Autres charges d\'exploitation',    'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '651',     'intitule' => 'Pertes sur créances irrécouvrables','classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '658',     'intitule' => 'Charges diverses d\'exploitation',  'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '66',      'intitule' => 'Charges financières',               'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '661',     'intitule' => 'Intérêts des emprunts',             'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '662',     'intitule' => 'Agios bancaires',                   'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '663',     'intitule' => 'Pertes de change',                  'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '668',     'intitule' => 'Autres charges financières',        'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '67',      'intitule' => 'Charges exceptionnelles',           'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '671',     'intitule' => 'Charges exceptionnelles sur opérations de gestion', 'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '672',     'intitule' => 'Charges exceptionnelles sur opérations en capital', 'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '673',     'intitule' => 'Pénalités et amendes exceptionnelles','classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '68',      'intitule' => 'Dotations aux amortissements',      'classe' => '6', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '681',     'intitule' => 'Dotations aux amortissements d\'exploitation', 'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '682',     'intitule' => 'Dotations aux amortissements financiers','classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '683',     'intitule' => 'Dotations aux amortissements exceptionnels','classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],
        ['code' => '685',     'intitule' => 'Dotations aux provisions',          'classe' => '6', 'niveau' => 3, 'solde_debiteur' => true],

        // ═══════════════════ CLASSE 7 — PRODUITS ═══════════════════
        ['code' => '701',     'intitule' => 'Ventes de marchandises',            'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '7011',    'intitule' => 'Ventes de marchandises — locales',  'classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '7012',    'intitule' => 'Ventes de marchandises — exportation','classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '702',     'intitule' => 'Ventes de produits finis',          'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '703',     'intitule' => 'Prestations de services',           'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '7031',    'intitule' => 'Prestations de services — locales', 'classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '7032',    'intitule' => 'Prestations de services — exportation','classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '704',     'intitule' => 'Locations',                         'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '705',     'intitule' => 'Produits des cessions d\'actifs',   'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '706',     'intitule' => 'Produits de placement',             'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '707',     'intitule' => 'Revenus des immeubles',             'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '708',     'intitule' => 'Rabais, remises et ristournes accordés','classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '71',      'intitule' => 'Subventions d\'exploitation',       'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '711',     'intitule' => 'Subventions d\'exploitation — État','classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '72',      'intitule' => 'Autres produits d\'exploitation',   'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '73',      'intitule' => 'Produits financiers',               'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '731',     'intitule' => 'Intérêts et produits assimilés',    'classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '732',     'intitule' => 'Gains de change',                   'classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '733',     'intitule' => 'Revenus des titres de placement',   'classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '74',      'intitule' => 'Produits exceptionnels',            'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '741',     'intitule' => 'Produits exceptionnels sur opérations de gestion','classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '742',     'intitule' => 'Produits exceptionnels sur opérations en capital','classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '75',      'intitule' => 'Transferts de charges',             'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '78',      'intitule' => 'Reprises de provisions',            'classe' => '7', 'niveau' => 2, 'solde_debiteur' => false],
        ['code' => '781',     'intitule' => 'Reprises de provisions d\'exploitation','classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '782',     'intitule' => 'Reprises de provisions financières','classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],
        ['code' => '783',     'intitule' => 'Reprises de provisions exceptionnelles','classe' => '7', 'niveau' => 3, 'solde_debiteur' => false],

        // ═══════════════════ CLASSE 8 — RÉSULTATS ═══════════════════
        ['code' => '801',     'intitule' => 'Résultat d\'exploitation',          'classe' => '8', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '802',     'intitule' => 'Résultat financier',                'classe' => '8', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '803',     'intitule' => 'Résultat courant',                  'classe' => '8', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '804',     'intitule' => 'Résultat exceptionnel',             'classe' => '8', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '805',     'intitule' => 'Résultat net',                      'classe' => '8', 'niveau' => 2, 'solde_debiteur' => true],
        ['code' => '809',     'intitule' => 'Résultat en instance d\'affectation','classe' => '8', 'niveau' => 2, 'solde_debiteur' => true],
    ];

    /**
     * Remplit la table gel_comptes_comptables avec les comptes SYSCOHADA.
     * Associe chaque compte au premier cabinet s'il existe.
     * Les comptes sont créés sans client_id (plan de référence partagé).
     */
    public function run(): void
    {
        $cabinet = Cabinet::firstOrCreate(
            ['slug' => 'gel-cabinet'],
            [
                'nom' => 'GEL Cabinet',
                'email' => 'contact@gel.cabinet',
                'telephone' => '+229 00000000',
                'actif' => true
            ]
        );
        $now = now();

        if (!$cabinet) {
            $this->command?->warn('Aucun cabinet trouvé. Les comptes seront créés sans cabinet_id.');
        }

        $comptesAInserer = [];
        foreach ($this->comptes as $compte) {
            $comptesAInserer[] = [
                'cabinet_id' => $cabinet?->id,
                'client_id' => null,
                'code' => $compte['code'],
                'intitule' => $compte['intitule'],
                'classe' => $compte['classe'],
                'niveau' => $compte['niveau'],
                'code_parent' => null,
                'actif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insérer par lots de 50
        foreach (array_chunk($comptesAInserer, 50) as $chunk) {
            CompteComptable::insert($chunk);
        }

        // Mettre à jour les comptes parents (hiérarchie)
        $this->updateParentRelations($cabinet?->id);

        $total = count($comptesAInserer);
        $this->command?->info("✓ Plan comptable SYSCOHADA créé : {$total} comptes.");

        // Créer les journaux standards du cabinet
        $this->createJournaux($cabinet?->id);

        // Créer l'exercice en cours pour chaque client du cabinet
        $this->createExercices($cabinet?->id);
    }

    private function updateParentRelations(?int $cabinetId): void
    {
        $comptes = CompteComptable::when($cabinetId, fn($q) => $q->where('cabinet_id', $cabinetId))
            ->orderBy('code')
            ->get();

        foreach ($comptes as $compte) {
            $codeParent = $this->findParentCode($compte->code);
            if ($codeParent) {
                $compte->update(['code_parent' => $codeParent]);
            }
        }
    }

    /**
     * Trouve le code du compte parent basé sur la logique SYSCOHADA.
     * Ex: 4011 → parent 401 ; 401 → parent 40 → parent 4.
     */
    private function findParentCode(string $code): ?string
    {
        $length = strlen($code);

        // Codes longs (niveau 3+) → parent niveau 2
        if ($length >= 4) {
            return substr($code, 0, -1); // 4011 → 401
        }

        // Codes de niveau 2 → parent = premier chiffre
        if ($length >= 2) {
            return substr($code, 0, 1); // 401 → 4
        }

        return null;
    }

    private function createJournaux(?int $cabinetId): void
    {
        if (!$cabinetId) {
            $this->command?->warn('Aucun cabinet, journaux non créés.');
            return;
        }

        $journaux = [
            ['code' => 'AC', 'libelle' => 'Journal des achats',                     'type' => 'achats'],
            ['code' => 'VE', 'libelle' => 'Journal des ventes',                     'type' => 'ventes'],
            ['code' => 'BN', 'libelle' => 'Journal des banques',                    'type' => 'banque'],
            ['code' => 'CA', 'libelle' => 'Journal de caisse',                      'type' => 'caisse'],
            ['code' => 'OD', 'libelle' => 'Journal des opérations diverses',        'type' => 'divers'],
            ['code' => 'AN', 'libelle' => 'Journal des engagements hors bilan',     'type' => 'hors-bilan'],
            ['code' => 'SA', 'libelle' => 'Journal des salaires',                   'type' => 'paie'],
            ['code' => 'IM', 'libelle' => 'Journal des immobilisations',            'type' => 'immobilisations'],
        ];

        $existing = \App\Models\Gel\Comptabilite\Journal::where('cabinet_id', $cabinetId)->count();
        if ($existing > 0) {
            $this->command?->info("  ~ Journaux déjà existants pour ce cabinet ({$existing}).");
            return;
        }

        foreach ($journaux as $journal) {
            \App\Models\Gel\Comptabilite\Journal::create([
                'cabinet_id' => $cabinetId,
                'code' => $journal['code'],
                'libelle' => $journal['libelle'],
                'type' => $journal['type'],
                'actif' => true,
            ]);
        }

        $this->command?->info('✓ 8 journaux standards créés (AC, VE, BN, CA, OD, AN, SA, IM).');
    }

    private function createExercices(?int $cabinetId): void
    {
        if (!$cabinetId) {
            return;
        }

        $clients = \App\Models\Client::whereDoesntHave('gelExercices')->get();

        if ($clients->isEmpty()) {
            $this->command?->info('  ~ Tous les clients ont déjà des exercices.');
            return;
        }

        $compteur = 0;
        foreach ($clients as $client) {
            $debut = now()->startOfYear();
            $fin = now()->endOfYear();

            \App\Models\Gel\Comptabilite\ExerciceComptable::create([
                'cabinet_id' => $cabinetId,
                'client_id' => $client->id,
                'libelle' => "Exercice {$debut->format('Y')}",
                'date_debut' => $debut->format('Y-m-d'),
                'date_fin' => $fin->format('Y-m-d'),
                'cloture' => false,
            ]);
            $compteur++;
        }

        if ($compteur > 0) {
            $this->command?->info("✓ {$compteur} exercice(s) créé(s).");
        }
    }
}
