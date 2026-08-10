<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gel\PlanComptableSyscohada;

class SyscohadaSeeder extends Seeder
{
    public function run(): void
    {
        $comptes = [
            // Classe 1: Comptes de ressources durables
            ['numero' => '1', 'libelle' => 'COMPTES DE RESSOURCES DURABLES', 'classe' => 1],
            ['numero' => '10', 'libelle' => 'Capital', 'classe' => 1],
            ['numero' => '11', 'libelle' => 'Réserves', 'classe' => 1],
            ['numero' => '12', 'libelle' => 'Report à nouveau', 'classe' => 1],
            ['numero' => '13', 'libelle' => 'Résultat net de l\'exercice', 'classe' => 1],
            ['numero' => '14', 'libelle' => 'Subventions d\'investissement', 'classe' => 1],
            ['numero' => '15', 'libelle' => 'Provisions réglementées et fonds assimilés', 'classe' => 1],
            ['numero' => '16', 'libelle' => 'Emprunts et dettes assimilées', 'classe' => 1],
            ['numero' => '17', 'libelle' => 'Dettes de crédit-bail et contrats assimilés', 'classe' => 1],
            ['numero' => '18', 'libelle' => 'Dettes liées à des participations', 'classe' => 1],
            ['numero' => '19', 'libelle' => 'Provisions financières pour risques et charges', 'classe' => 1],
            
            // Classe 2: Comptes d'actif immobilisé
            ['numero' => '2', 'libelle' => 'COMPTES D\'ACTIF IMMOBILISÉ', 'classe' => 2],
            ['numero' => '20', 'libelle' => 'Charges immobilisées', 'classe' => 2],
            ['numero' => '21', 'libelle' => 'Immobilisations incorporelles', 'classe' => 2],
            ['numero' => '22', 'libelle' => 'Terrains', 'classe' => 2],
            ['numero' => '23', 'libelle' => 'Bâtiments, installations techniques et agencements', 'classe' => 2],
            ['numero' => '24', 'libelle' => 'Matériel, mobilier et actifs biologiques', 'classe' => 2],
            ['numero' => '25', 'libelle' => 'Avances et acomptes versés sur immobilisations', 'classe' => 2],
            ['numero' => '26', 'libelle' => 'Titres de participation', 'classe' => 2],
            ['numero' => '27', 'libelle' => 'Autres immobilisations financières', 'classe' => 2],
            ['numero' => '28', 'libelle' => 'Amortissements', 'classe' => 2],
            ['numero' => '29', 'libelle' => 'Dépréciations', 'classe' => 2],

            // Classe 3: Comptes de stocks
            ['numero' => '3', 'libelle' => 'COMPTES DE STOCKS', 'classe' => 3],
            ['numero' => '31', 'libelle' => 'Marchandises', 'classe' => 3],
            ['numero' => '32', 'libelle' => 'Matières premières et fournitures liées', 'classe' => 3],
            ['numero' => '33', 'libelle' => 'Autres approvisionnements', 'classe' => 3],
            ['numero' => '34', 'libelle' => 'Produits en cours', 'classe' => 3],
            ['numero' => '35', 'libelle' => 'Services en cours', 'classe' => 3],
            ['numero' => '36', 'libelle' => 'Produits finis', 'classe' => 3],
            ['numero' => '37', 'libelle' => 'Produits intermédiaires et résiduels', 'classe' => 3],
            ['numero' => '38', 'libelle' => 'Stocks en cours de route', 'classe' => 3],
            ['numero' => '39', 'libelle' => 'Dépréciations des stocks', 'classe' => 3],

            // Classe 4: Comptes de tiers
            ['numero' => '4', 'libelle' => 'COMPTES DE TIERS', 'classe' => 4],
            ['numero' => '40', 'libelle' => 'Fournisseurs et comptes rattachés', 'classe' => 4],
            ['numero' => '41', 'libelle' => 'Clients et comptes rattachés', 'classe' => 4],
            ['numero' => '42', 'libelle' => 'Personnel', 'classe' => 4],
            ['numero' => '43', 'libelle' => 'Organismes sociaux', 'classe' => 4],
            ['numero' => '44', 'libelle' => 'État et collectivités publiques', 'classe' => 4],
            ['numero' => '45', 'libelle' => 'Organismes internationaux et autres', 'classe' => 4],
            ['numero' => '46', 'libelle' => 'Associés et groupe', 'classe' => 4],
            ['numero' => '47', 'libelle' => 'Débiteurs et créditeurs divers', 'classe' => 4],
            ['numero' => '48', 'libelle' => 'Créances et dettes hors activités ordinaires (HAO)', 'classe' => 4],
            ['numero' => '49', 'libelle' => 'Dépréciations des comptes de tiers', 'classe' => 4],

            // Classe 5: Comptes de trésorerie
            ['numero' => '5', 'libelle' => 'COMPTES DE TRÉSORERIE', 'classe' => 5],
            ['numero' => '50', 'libelle' => 'Titres de placement', 'classe' => 5],
            ['numero' => '51', 'libelle' => 'Valeurs à l\'encaissement', 'classe' => 5],
            ['numero' => '52', 'libelle' => 'Banques', 'classe' => 5],
            ['numero' => '53', 'libelle' => 'Établissements financiers', 'classe' => 5],
            ['numero' => '54', 'libelle' => 'Instruments de trésorerie', 'classe' => 5],
            ['numero' => '56', 'libelle' => 'Banques, crédits de trésorerie', 'classe' => 5],
            ['numero' => '57', 'libelle' => 'Caisse', 'classe' => 5],
            ['numero' => '58', 'libelle' => 'Régies d\'avance et virements internes', 'classe' => 5],
            ['numero' => '59', 'libelle' => 'Dépréciations des comptes de trésorerie', 'classe' => 5],

            // Classe 6: Comptes de charges des activités ordinaires
            ['numero' => '6', 'libelle' => 'COMPTES DE CHARGES DES ACTIVITÉS ORDINAIRES', 'classe' => 6],
            ['numero' => '60', 'libelle' => 'Achats et variations de stocks', 'classe' => 6],
            ['numero' => '61', 'libelle' => 'Transports', 'classe' => 6],
            ['numero' => '62', 'libelle' => 'Services extérieurs A', 'classe' => 6],
            ['numero' => '63', 'libelle' => 'Services extérieurs B', 'classe' => 6],
            ['numero' => '64', 'libelle' => 'Impôts et taxes', 'classe' => 6],
            ['numero' => '65', 'libelle' => 'Autres charges', 'classe' => 6],
            ['numero' => '66', 'libelle' => 'Charges de personnel', 'classe' => 6],
            ['numero' => '67', 'libelle' => 'Frais financiers', 'classe' => 6],
            ['numero' => '68', 'libelle' => 'Dotations aux amortissements', 'classe' => 6],
            ['numero' => '69', 'libelle' => 'Dotations aux dépréciations et provisions', 'classe' => 6],

            // Classe 7: Comptes de produits des activités ordinaires
            ['numero' => '7', 'libelle' => 'COMPTES DE PRODUITS DES ACTIVITÉS ORDINAIRES', 'classe' => 7],
            ['numero' => '70', 'libelle' => 'Ventes', 'classe' => 7],
            ['numero' => '71', 'libelle' => 'Subventions d\'exploitation', 'classe' => 7],
            ['numero' => '72', 'libelle' => 'Production immobilisée', 'classe' => 7],
            ['numero' => '73', 'libelle' => 'Variations de stocks de biens et services', 'classe' => 7],
            ['numero' => '75', 'libelle' => 'Autres produits', 'classe' => 7],
            ['numero' => '77', 'libelle' => 'Revenus financiers', 'classe' => 7],
            ['numero' => '78', 'libelle' => 'Transferts de charges', 'classe' => 7],
            ['numero' => '79', 'libelle' => 'Reprises de dépréciations et provisions', 'classe' => 7],

            // Classe 8: Comptes des autres charges et autres produits
            ['numero' => '8', 'libelle' => 'COMPTES DES AUTRES CHARGES ET AUTRES PRODUITS', 'classe' => 8],
            ['numero' => '81', 'libelle' => 'Valeurs comptables des cessions d\'immobilisations', 'classe' => 8],
            ['numero' => '82', 'libelle' => 'Produits des cessions d\'immobilisations', 'classe' => 8],
            ['numero' => '83', 'libelle' => 'Charges hors activités ordinaires', 'classe' => 8],
            ['numero' => '84', 'libelle' => 'Produits hors activités ordinaires', 'classe' => 8],
            ['numero' => '85', 'libelle' => 'Dotations hors activités ordinaires', 'classe' => 8],
            ['numero' => '86', 'libelle' => 'Reprises hors activités ordinaires', 'classe' => 8],
            ['numero' => '87', 'libelle' => 'Participation des travailleurs', 'classe' => 8],
            ['numero' => '88', 'libelle' => 'Subventions d\'équilibre', 'classe' => 8],
            ['numero' => '89', 'libelle' => 'Impôts sur le résultat', 'classe' => 8],

            // Classe 9: Comptes des engagements hors bilan et compta analytique
            ['numero' => '9', 'libelle' => 'COMPTABILITÉ DES ENGAGEMENTS / ANALYTIQUE', 'classe' => 9],
        ];

        foreach ($comptes as $compte) {
            PlanComptableSyscohada::updateOrCreate(
                ['numero' => $compte['numero']],
                $compte
            );
        }
    }
}
