<?php

namespace Database\Seeders\Gel;

use App\Models\Gel\CompteComptable;
use Illuminate\Database\Seeder;

class PlanComptableSyscohadaSeeder extends Seeder
{
    public function run(): void
    {
        $cabinetId = 1; // Premier cabinet créé

        $comptes = [
            // CLASSE 1 — COMPTES DE RESSOURCES (PASSIF)
            ['code' => '1', 'intitule' => 'CLASSE 1 : Comptes de ressources', 'classe' => '1', 'type' => 'passif', 'niveau' => 0, 'syscohada' => true],
            ['code' => '10', 'intitule' => 'Capital et réserves', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],
            ['code' => '101', 'intitule' => 'Capital social', 'classe' => '1', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '10', 'syscohada' => true],
            ['code' => '1011', 'intitule' => 'Capital souscrit', 'classe' => '1', 'type' => 'passif', 'niveau' => 3, 'code_parent' => '101', 'syscohada' => true],
            ['code' => '1012', 'intitule' => 'Capital appelé, non versé', 'classe' => '1', 'type' => 'passif', 'niveau' => 3, 'code_parent' => '101', 'syscohada' => true],
            ['code' => '1013', 'intitule' => 'Capital appelé, versé', 'classe' => '1', 'type' => 'passif', 'niveau' => 3, 'code_parent' => '101', 'syscohada' => true],
            ['code' => '106', 'intitule' => 'Réserves', 'classe' => '1', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '10', 'syscohada' => true],
            ['code' => '1061', 'intitule' => 'Réserve légale', 'classe' => '1', 'type' => 'passif', 'niveau' => 3, 'code_parent' => '106', 'syscohada' => true],
            ['code' => '1063', 'intitule' => 'Réserves statutaires', 'classe' => '1', 'type' => 'passif', 'niveau' => 3, 'code_parent' => '106', 'syscohada' => true],
            ['code' => '1068', 'intitule' => 'Autres réserves', 'classe' => '1', 'type' => 'passif', 'niveau' => 3, 'code_parent' => '106', 'syscohada' => true],
            ['code' => '11', 'intitule' => 'Report à nouveau', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],
            ['code' => '12', 'intitule' => 'Résultat net de l\'exercice', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],
            ['code' => '13', 'intitule' => 'Subventions d\'investissement', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],
            ['code' => '14', 'intitule' => 'Provisions réglementées', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],
            ['code' => '15', 'intitule' => 'Emprunts et dettes assimilées', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],
            ['code' => '151', 'intitule' => 'Emprunts obligataires', 'classe' => '1', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '15', 'syscohada' => true],
            ['code' => '154', 'intitule' => 'Emprunts auprès des établissements de crédit', 'classe' => '1', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '15', 'syscohada' => true],
            ['code' => '155', 'intitule' => 'Avances reçues de l\'État', 'classe' => '1', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '15', 'syscohada' => true],
            ['code' => '16', 'intitule' => 'Provisions pour passif', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],
            ['code' => '17', 'intitule' => 'Dettes de crédit-bail', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],
            ['code' => '18', 'intitule' => 'Comptes de liaison des établissements', 'classe' => '1', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '1', 'syscohada' => true],

            // CLASSE 2 — COMPTES D'IMMOBILISATIONS (ACTIF)
            ['code' => '2', 'intitule' => 'CLASSE 2 : Comptes d\'immobilisations', 'classe' => '2', 'type' => 'actif', 'niveau' => 0, 'syscohada' => true],
            ['code' => '20', 'intitule' => 'Immobilisations incorporelles', 'classe' => '2', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '2', 'syscohada' => true],
            ['code' => '201', 'intitule' => 'Frais d\'établissement', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '20', 'syscohada' => true],
            ['code' => '202', 'intitule' => 'Frais de recherche et développement', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '20', 'syscohada' => true],
            ['code' => '203', 'intitule' => 'Brevets, licences, marques', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '20', 'syscohada' => true],
            ['code' => '204', 'intitule' => 'Fonds commercial', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '20', 'syscohada' => true],
            ['code' => '205', 'intitule' => 'Logiciels', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '20', 'syscohada' => true],
            ['code' => '21', 'intitule' => 'Immobilisations corporelles', 'classe' => '2', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '2', 'syscohada' => true],
            ['code' => '211', 'intitule' => 'Terrains', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '21', 'syscohada' => true],
            ['code' => '212', 'intitule' => 'Constructions', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '21', 'syscohada' => true],
            ['code' => '213', 'intitule' => 'Installations techniques', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '21', 'syscohada' => true],
            ['code' => '214', 'intitule' => 'Matériel de transport', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '21', 'syscohada' => true],
            ['code' => '215', 'intitule' => 'Mobilier et matériel de bureau', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '21', 'syscohada' => true],
            ['code' => '218', 'intitule' => 'Autres immobilisations corporelles', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '21', 'syscohada' => true],
            ['code' => '22', 'intitule' => 'Immobilisations mises en concession', 'classe' => '2', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '2', 'syscohada' => true],
            ['code' => '23', 'intitule' => 'Immobilisations en cours', 'classe' => '2', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '2', 'syscohada' => true],
            ['code' => '24', 'intitule' => 'Immobilisations financières', 'classe' => '2', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '2', 'syscohada' => true],
            ['code' => '241', 'intitule' => 'Titres de participation', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '24', 'syscohada' => true],
            ['code' => '242', 'intitule' => 'Prêts immobiliers', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '24', 'syscohada' => true],
            ['code' => '243', 'intitule' => 'Prêts au personnel', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '24', 'syscohada' => true],
            ['code' => '244', 'intitule' => 'Créances sur l\'État', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '24', 'syscohada' => true],
            ['code' => '245', 'intitule' => 'Dépôts et cautionnements', 'classe' => '2', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '24', 'syscohada' => true],

            // CLASSE 3 — COMPTES DE STOCKS ET EN-COURS
            ['code' => '3', 'intitule' => 'CLASSE 3 : Comptes de stocks', 'classe' => '3', 'type' => 'actif', 'niveau' => 0, 'syscohada' => true],
            ['code' => '31', 'intitule' => 'Stocks de marchandises', 'classe' => '3', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '3', 'syscohada' => true],
            ['code' => '311', 'intitule' => 'Marchandises A', 'classe' => '3', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '31', 'syscohada' => true],
            ['code' => '312', 'intitule' => 'Marchandises B', 'classe' => '3', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '31', 'syscohada' => true],
            ['code' => '32', 'intitule' => 'Stocks de matières premières', 'classe' => '3', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '3', 'syscohada' => true],
            ['code' => '33', 'intitule' => 'Stocks de matières consommables', 'classe' => '3', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '3', 'syscohada' => true],
            ['code' => '34', 'intitule' => 'Stocks de produits en cours', 'classe' => '3', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '3', 'syscohada' => true],
            ['code' => '35', 'intitule' => 'Stocks de produits finis', 'classe' => '3', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '3', 'syscohada' => true],
            ['code' => '36', 'intitule' => 'Stocks de produits résiduels', 'classe' => '3', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '3', 'syscohada' => true],

            // CLASSE 4 — COMPTES DE TIERS
            ['code' => '4', 'intitule' => 'CLASSE 4 : Comptes de tiers', 'classe' => '4', 'type' => 'actif', 'niveau' => 0, 'syscohada' => true],
            ['code' => '41', 'intitule' => 'Fournisseurs', 'classe' => '4', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '4', 'syscohada' => true],
            ['code' => '411', 'intitule' => 'Fournisseurs d\'exploitation', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '41', 'syscohada' => true],
            ['code' => '412', 'intitule' => 'Fournisseurs d\'immobilisations', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '41', 'syscohada' => true],
            ['code' => '413', 'intitule' => 'Fournisseurs, factures non parvenues', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '41', 'syscohada' => true],
            ['code' => '416', 'intitule' => 'Créances sur cessions d\'immobilisations', 'classe' => '4', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '41', 'syscohada' => true],
            ['code' => '42', 'intitule' => 'Personnel', 'classe' => '4', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '4', 'syscohada' => true],
            ['code' => '421', 'intitule' => 'Personnel, rémunérations dues', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '42', 'syscohada' => true],
            ['code' => '422', 'intitule' => 'Personnel, charges à payer', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '42', 'syscohada' => true],
            ['code' => '428', 'intitule' => 'Personnel, autres dettes', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '42', 'syscohada' => true],
            ['code' => '43', 'intitule' => 'Sécurité sociale', 'classe' => '4', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '4', 'syscohada' => true],
            ['code' => '431', 'intitule' => 'Sécurité sociale, cotisations dues', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '43', 'syscohada' => true],
            ['code' => '44', 'intitule' => 'État', 'classe' => '4', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '4', 'syscohada' => true],
            ['code' => '441', 'intitule' => 'État, impôts sur les résultats', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '44', 'syscohada' => true],
            ['code' => '442', 'intitule' => 'État, autres impôts et taxes', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '44', 'syscohada' => true],
            ['code' => '443', 'intitule' => 'État, TVA collectée', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '44', 'syscohada' => true],
            ['code' => '444', 'intitule' => 'État, TVA déductible', 'classe' => '4', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '44', 'syscohada' => true],
            ['code' => '445', 'intitule' => 'État, TVA à reverser', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '44', 'syscohada' => true],
            ['code' => '447', 'intitule' => 'État, autres dettes fiscales', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '44', 'syscohada' => true],
            ['code' => '45', 'intitule' => 'Groupes et associés', 'classe' => '4', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '4', 'syscohada' => true],
            ['code' => '46', 'intitule' => 'Clients', 'classe' => '4', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '4', 'syscohada' => true],
            ['code' => '461', 'intitule' => 'Clients, ventes de biens', 'classe' => '4', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '46', 'syscohada' => true],
            ['code' => '462', 'intitule' => 'Clients, prestations de services', 'classe' => '4', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '46', 'syscohada' => true],
            ['code' => '463', 'intitule' => 'Clients, factures à établir', 'classe' => '4', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '46', 'syscohada' => true],
            ['code' => '464', 'intitule' => 'Clients, effets à recevoir', 'classe' => '4', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '46', 'syscohada' => true],
            ['code' => '465', 'intitule' => 'Clients, avances et acomptes', 'classe' => '4', 'type' => 'passif', 'niveau' => 2, 'code_parent' => '46', 'syscohada' => true],
            ['code' => '466', 'intitule' => 'Clients, créances douteuses', 'classe' => '4', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '46', 'syscohada' => true],
            ['code' => '47', 'intitule' => 'Comptes transitoires', 'classe' => '4', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '4', 'syscohada' => true],
            ['code' => '48', 'intitule' => 'Créances et dettes diverses', 'classe' => '4', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '4', 'syscohada' => true],

            // CLASSE 5 — COMPTES DE TRESORERIE
            ['code' => '5', 'intitule' => 'CLASSE 5 : Comptes de trésorerie', 'classe' => '5', 'type' => 'actif', 'niveau' => 0, 'syscohada' => true],
            ['code' => '51', 'intitule' => 'Valeurs assimilées à la trésorerie', 'classe' => '5', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '5', 'syscohada' => true],
            ['code' => '52', 'intitule' => 'Banques', 'classe' => '5', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '5', 'syscohada' => true],
            ['code' => '521', 'intitule' => 'Banque A', 'classe' => '5', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '52', 'syscohada' => true],
            ['code' => '522', 'intitule' => 'Banque B', 'classe' => '5', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '52', 'syscohada' => true],
            ['code' => '53', 'intitule' => 'Caisse', 'classe' => '5', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '5', 'syscohada' => true],
            ['code' => '531', 'intitule' => 'Caisse principale', 'classe' => '5', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '53', 'syscohada' => true],
            ['code' => '532', 'intitule' => 'Caisse de caisse', 'classe' => '5', 'type' => 'actif', 'niveau' => 2, 'code_parent' => '53', 'syscohada' => true],
            ['code' => '54', 'intitule' => 'Régies d\'avance', 'classe' => '5', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '5', 'syscohada' => true],

            // CLASSE 6 — COMPTES DE CHARGES
            ['code' => '6', 'intitule' => 'CLASSE 6 : Comptes de charges', 'classe' => '6', 'type' => 'charge', 'niveau' => 0, 'syscohada' => true],
            ['code' => '60', 'intitule' => 'Achats', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],
            ['code' => '601', 'intitule' => 'Achats de marchandises', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '60', 'syscohada' => true],
            ['code' => '602', 'intitule' => 'Achats de matières premières', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '60', 'syscohada' => true],
            ['code' => '603', 'intitule' => 'Achats de matières consommables', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '60', 'syscohada' => true],
            ['code' => '604', 'intitule' => 'Achats d\'emballages', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '60', 'syscohada' => true],
            ['code' => '605', 'intitule' => 'Achats de fournitures', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '60', 'syscohada' => true],
            ['code' => '61', 'intitule' => 'Transports', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],
            ['code' => '62', 'intitule' => 'Services extérieurs', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],
            ['code' => '621', 'intitule' => 'Loyers', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '62', 'syscohada' => true],
            ['code' => '622', 'intitule' => 'Entretien et réparations', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '62', 'syscohada' => true],
            ['code' => '623', 'intitule' => 'Électricité, eau, gaz', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '62', 'syscohada' => true],
            ['code' => '624', 'intitule' => 'Télécommunications', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '62', 'syscohada' => true],
            ['code' => '625', 'intitule' => 'Assurances', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '62', 'syscohada' => true],
            ['code' => '626', 'intitule' => 'Honoraires', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '62', 'syscohada' => true],
            ['code' => '627', 'intitule' => 'Publicité et relations publiques', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '62', 'syscohada' => true],
            ['code' => '628', 'intitule' => 'Frais postaux', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '62', 'syscohada' => true],
            ['code' => '63', 'intitule' => 'Impôts et taxes', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],
            ['code' => '631', 'intitule' => 'Impôts et taxes directs', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '63', 'syscohada' => true],
            ['code' => '632', 'intitule' => 'Impôts et taxes indirects', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '63', 'syscohada' => true],
            ['code' => '64', 'intitule' => 'Frais de personnel', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],
            ['code' => '641', 'intitule' => 'Salaires et traitements', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '64', 'syscohada' => true],
            ['code' => '642', 'intitule' => 'Charges sociales', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '64', 'syscohada' => true],
            ['code' => '643', 'intitule' => 'Autres charges de personnel', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '64', 'syscohada' => true],
            ['code' => '65', 'intitule' => 'Autres charges d\'exploitation', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],
            ['code' => '66', 'intitule' => 'Dotations aux amortissements', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],
            ['code' => '661', 'intitule' => 'Dotations aux amortissements', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '66', 'syscohada' => true],
            ['code' => '662', 'intitule' => 'Dotations aux provisions', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '66', 'syscohada' => true],
            ['code' => '67', 'intitule' => 'Frais financiers', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],
            ['code' => '671', 'intitule' => 'Intérêts des emprunts', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '67', 'syscohada' => true],
            ['code' => '672', 'intitule' => 'Escomptes accordés', 'classe' => '6', 'type' => 'charge', 'niveau' => 2, 'code_parent' => '67', 'syscohada' => true],
            ['code' => '68', 'intitule' => 'Charges exceptionnelles', 'classe' => '6', 'type' => 'charge', 'niveau' => 1, 'code_parent' => '6', 'syscohada' => true],

            // CLASSE 7 — COMPTES DE PRODUITS
            ['code' => '7', 'intitule' => 'CLASSE 7 : Comptes de produits', 'classe' => '7', 'type' => 'produit', 'niveau' => 0, 'syscohada' => true],
            ['code' => '70', 'intitule' => 'Ventes', 'classe' => '7', 'type' => 'produit', 'niveau' => 1, 'code_parent' => '7', 'syscohada' => true],
            ['code' => '701', 'intitule' => 'Ventes de marchandises', 'classe' => '7', 'type' => 'produit', 'niveau' => 2, 'code_parent' => '70', 'syscohada' => true],
            ['code' => '702', 'intitule' => 'Ventes de produits finis', 'classe' => '7', 'type' => 'produit', 'niveau' => 2, 'code_parent' => '70', 'syscohada' => true],
            ['code' => '703', 'intitule' => 'Prestations de services', 'classe' => '7', 'type' => 'produit', 'niveau' => 2, 'code_parent' => '70', 'syscohada' => true],
            ['code' => '704', 'intitule' => 'Produits accessoires', 'classe' => '7', 'type' => 'produit', 'niveau' => 2, 'code_parent' => '70', 'syscohada' => true],
            ['code' => '71', 'intitule' => 'Subventions d\'exploitation', 'classe' => '7', 'type' => 'produit', 'niveau' => 1, 'code_parent' => '7', 'syscohada' => true],
            ['code' => '72', 'intitule' => 'Production immobilisée', 'classe' => '7', 'type' => 'produit', 'niveau' => 1, 'code_parent' => '7', 'syscohada' => true],
            ['code' => '73', 'intitule' => 'Variations de stocks', 'classe' => '7', 'type' => 'produit', 'niveau' => 1, 'code_parent' => '7', 'syscohada' => true],
            ['code' => '74', 'intitule' => 'Autres produits d\'exploitation', 'classe' => '7', 'type' => 'produit', 'niveau' => 1, 'code_parent' => '7', 'syscohada' => true],
            ['code' => '75', 'intitule' => 'Produits financiers', 'classe' => '7', 'type' => 'produit', 'niveau' => 1, 'code_parent' => '7', 'syscohada' => true],
            ['code' => '751', 'intitule' => 'Intérêts et revenus assimilés', 'classe' => '7', 'type' => 'produit', 'niveau' => 2, 'code_parent' => '75', 'syscohada' => true],
            ['code' => '752', 'intitule' => 'Escomptes obtenus', 'classe' => '7', 'type' => 'produit', 'niveau' => 2, 'code_parent' => '75', 'syscohada' => true],
            ['code' => '76', 'intitule' => 'Produits exceptionnels', 'classe' => '7', 'type' => 'produit', 'niveau' => 1, 'code_parent' => '7', 'syscohada' => true],

            // CLASSE 8 — COMPTES SPECIAUX
            ['code' => '8', 'intitule' => 'CLASSE 8 : Comptes spéciaux', 'classe' => '8', 'type' => 'passif', 'niveau' => 0, 'syscohada' => true],
            ['code' => '81', 'intitule' => 'Engagements donnés', 'classe' => '8', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '8', 'syscohada' => true],
            ['code' => '82', 'intitule' => 'Engagements reçus', 'classe' => '8', 'type' => 'passif', 'niveau' => 1, 'code_parent' => '8', 'syscohada' => true],
            ['code' => '83', 'intitule' => 'Contrepartie des engagements', 'classe' => '8', 'type' => 'actif', 'niveau' => 1, 'code_parent' => '8', 'syscohada' => true],
        ];

        $now = now();
        foreach ($comptes as $compte) {
            CompteComptable::firstOrCreate(
                ['cabinet_id' => $cabinetId, 'code' => $compte['code']],
                array_merge($compte, [
                    'cabinet_id' => $cabinetId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
