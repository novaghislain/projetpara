<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;

class AccountingAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Ce seeder crée le plan comptable de référence
        // Les comptes seront dupliqués pour chaque client via un mécanisme séparé
        // Cette méthode sera appelée pour chaque client

        // Les comptes OHADA de base
        $accounts = [
            // Classe 1: CAPITAUX (equity)
            ['code' => '101', 'name' => 'Capital', 'type' => 'equity'],
            ['code' => '1011', 'name' => 'Capital social', 'type' => 'equity'],
            ['code' => '1061', 'name' => 'Réserves légales', 'type' => 'equity'],
            ['code' => '1063', 'name' => 'Réserves statutaires', 'type' => 'equity'],
            ['code' => '12', 'name' => 'Résultat de l\'exercice', 'type' => 'equity'],
            ['code' => '129', 'name' => 'Résultat net', 'type' => 'equity'],

            // Classe 2: IMMOBILISATIONS (asset)
            ['code' => '21', 'name' => 'Immobilisations corporelles', 'type' => 'asset'],
            ['code' => '211', 'name' => 'Terrains', 'type' => 'asset'],
            ['code' => '213', 'name' => 'Constructions', 'type' => 'asset'],
            ['code' => '215', 'name' => 'Matériel et outillage', 'type' => 'asset'],
            ['code' => '218', 'name' => 'Mobilier et matériel de bureau', 'type' => 'asset'],
            ['code' => '281', 'name' => 'Amortissements des immobilisations', 'type' => 'asset'],

            // Classe 3: STOCKS (asset)
            ['code' => '31', 'name' => 'Marchandises', 'type' => 'asset'],
            ['code' => '311', 'name' => 'Marchandises A', 'type' => 'asset'],
            ['code' => '32', 'name' => 'Matières premières', 'type' => 'asset'],

            // Classe 4: TIERS (asset/liability)
            ['code' => '401', 'name' => 'Fournisseurs', 'type' => 'liability'],
            ['code' => '4011', 'name' => 'Fournisseurs locaux', 'type' => 'liability'],
            ['code' => '411', 'name' => 'Clients', 'type' => 'asset'],
            ['code' => '4111', 'name' => 'Clients locaux', 'type' => 'asset'],
            ['code' => '421', 'name' => 'Personnel - Rémunérations dues', 'type' => 'liability'],
            ['code' => '431', 'name' => 'Sécurité sociale', 'type' => 'liability'],
            ['code' => '4311', 'name' => 'CNSS', 'type' => 'liability'],
            ['code' => '441', 'name' => 'Etat - Impôts et taxes', 'type' => 'liability'],
            ['code' => '4411', 'name' => 'TVA collectée', 'type' => 'liability'],
            ['code' => '4412', 'name' => 'TVA déductible', 'type' => 'asset'],
            ['code' => '4413', 'name' => 'Impôt sur les sociétés', 'type' => 'liability'],
            ['code' => '445', 'name' => 'Autres créances et dettes', 'type' => 'asset'],

            // Classe 5: TRESORERIE (asset)
            ['code' => '511', 'name' => 'Banques', 'type' => 'asset'],
            ['code' => '5111', 'name' => 'Banque locale', 'type' => 'asset'],
            ['code' => '512', 'name' => 'Banque - Compte courant', 'type' => 'asset'],
            ['code' => '53', 'name' => 'Caisse', 'type' => 'asset'],
            ['code' => '531', 'name' => 'Caisse principale', 'type' => 'asset'],

            // Classe 6: CHARGES (expense)
            ['code' => '601', 'name' => 'Achats de marchandises', 'type' => 'expense'],
            ['code' => '6011', 'name' => 'Achats de marchandises locales', 'type' => 'expense'],
            ['code' => '602', 'name' => 'Achats de matières premières', 'type' => 'expense'],
            ['code' => '61', 'name' => 'Transports', 'type' => 'expense'],
            ['code' => '611', 'name' => 'Transports sur achats', 'type' => 'expense'],
            ['code' => '62', 'name' => 'Services extérieurs', 'type' => 'expense'],
            ['code' => '621', 'name' => 'Loyers', 'type' => 'expense'],
            ['code' => '622', 'name' => 'Entretien et réparations', 'type' => 'expense'],
            ['code' => '623', 'name' => 'Assurances', 'type' => 'expense'],
            ['code' => '624', 'name' => 'Honoraires', 'type' => 'expense'],
            ['code' => '63', 'name' => 'Impôts et taxes', 'type' => 'expense'],
            ['code' => '64', 'name' => 'Charges de personnel', 'type' => 'expense'],
            ['code' => '641', 'name' => 'Salaires', 'type' => 'expense'],
            ['code' => '642', 'name' => 'Charges sociales', 'type' => 'expense'],
            ['code' => '65', 'name' => 'Autres charges', 'type' => 'expense'],
            ['code' => '66', 'name' => 'Charges financières', 'type' => 'expense'],
            ['code' => '661', 'name' => 'Intérêts bancaires', 'type' => 'expense'],
            ['code' => '67', 'name' => 'Dotations aux amortissements', 'type' => 'expense'],

            // Classe 7: PRODUITS (revenue)
            ['code' => '701', 'name' => 'Ventes de marchandises', 'type' => 'revenue'],
            ['code' => '7011', 'name' => 'Ventes locales', 'type' => 'revenue'],
            ['code' => '702', 'name' => 'Prestations de services', 'type' => 'revenue'],
            ['code' => '71', 'name' => 'Subventions', 'type' => 'revenue'],
            ['code' => '72', 'name' => 'Produits financiers', 'type' => 'revenue'],
            ['code' => '73', 'name' => 'Autres produits', 'type' => 'revenue'],
            ['code' => '75', 'name' => 'Produits exceptionnels', 'type' => 'revenue'],
        ];

        foreach ($accounts as $account) {
            AccountingAccount::create(array_merge($account, ['client_id' => 0]));
        }
    }

    /**
     * Crée le plan comptable pour un client spécifique.
     */
    public static function createForClient(int $clientId, ?int $userId = null): void
    {
        $accounts = (new self)->getDefaultAccounts();
        foreach ($accounts as $account) {
            AccountingAccount::firstOrCreate([
                'client_id' => $clientId,
                'code' => $account['code'],
            ], [
                'name' => $account['name'],
                'type' => $account['type'],
                'is_active' => true,
            ]);
        }
    }

    private function getDefaultAccounts(): array
    {
        return [
            // Classe 1: CAPITAUX
            ['code' => '101', 'name' => 'Capital', 'type' => 'equity'],
            ['code' => '1011', 'name' => 'Capital social', 'type' => 'equity'],
            ['code' => '1061', 'name' => 'Réserves légales', 'type' => 'equity'],
            ['code' => '12', 'name' => 'Résultat de l\'exercice', 'type' => 'equity'],
            ['code' => '129', 'name' => 'Résultat net', 'type' => 'equity'],

            // Classe 2: IMMOBILISATIONS
            ['code' => '21', 'name' => 'Immobilisations corporelles', 'type' => 'asset'],
            ['code' => '213', 'name' => 'Constructions', 'type' => 'asset'],
            ['code' => '215', 'name' => 'Matériel et outillage', 'type' => 'asset'],
            ['code' => '218', 'name' => 'Mobilier et matériel de bureau', 'type' => 'asset'],

            // Classe 3: STOCKS
            ['code' => '31', 'name' => 'Marchandises', 'type' => 'asset'],
            ['code' => '32', 'name' => 'Matières premières', 'type' => 'asset'],

            // Classe 4: TIERS
            ['code' => '401', 'name' => 'Fournisseurs', 'type' => 'liability'],
            ['code' => '411', 'name' => 'Clients', 'type' => 'asset'],
            ['code' => '421', 'name' => 'Personnel - Rémunérations dues', 'type' => 'liability'],
            ['code' => '431', 'name' => 'Sécurité sociale (CNSS)', 'type' => 'liability'],
            ['code' => '441', 'name' => 'Etat - Impôts et taxes', 'type' => 'liability'],
            ['code' => '4411', 'name' => 'TVA collectée', 'type' => 'liability'],
            ['code' => '4412', 'name' => 'TVA déductible', 'type' => 'asset'],
            ['code' => '4413', 'name' => 'Impôt sur les sociétés (IS)', 'type' => 'liability'],

            // Classe 5: TRESORERIE
            ['code' => '512', 'name' => 'Banque - Compte courant', 'type' => 'asset'],
            ['code' => '531', 'name' => 'Caisse principale', 'type' => 'asset'],

            // Classe 6: CHARGES
            ['code' => '601', 'name' => 'Achats de marchandises', 'type' => 'expense'],
            ['code' => '602', 'name' => 'Achats de matières premières', 'type' => 'expense'],
            ['code' => '61', 'name' => 'Transports', 'type' => 'expense'],
            ['code' => '621', 'name' => 'Loyers', 'type' => 'expense'],
            ['code' => '622', 'name' => 'Entretien et réparations', 'type' => 'expense'],
            ['code' => '623', 'name' => 'Assurances', 'type' => 'expense'],
            ['code' => '624', 'name' => 'Honoraires', 'type' => 'expense'],
            ['code' => '63', 'name' => 'Impôts et taxes', 'type' => 'expense'],
            ['code' => '641', 'name' => 'Salaires', 'type' => 'expense'],
            ['code' => '642', 'name' => 'Charges sociales', 'type' => 'expense'],
            ['code' => '66', 'name' => 'Charges financières', 'type' => 'expense'],
            ['code' => '67', 'name' => 'Dotations aux amortissements', 'type' => 'expense'],

            // Classe 7: PRODUITS
            ['code' => '701', 'name' => 'Ventes de marchandises', 'type' => 'revenue'],
            ['code' => '702', 'name' => 'Prestations de services', 'type' => 'revenue'],
            ['code' => '72', 'name' => 'Produits financiers', 'type' => 'revenue'],
            ['code' => '75', 'name' => 'Produits exceptionnels', 'type' => 'revenue'],
        ];
    }
}
