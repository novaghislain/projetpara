<?php

namespace Database\Factories;

use App\Models\AccountingAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountingAccountFactory extends Factory
{
    protected $model = AccountingAccount::class;

    private static array $syscohadaAccounts = [
        ['101', 'Capital social', 'passive', 1],
        ['106', 'Réserves', 'passive', 1],
        ['131', 'Résultat net', 'passive', 1],
        ['164', 'Emprunts bancaires', 'passive', 1],
        ['201', "Frais d'établissement", 'active', 2],
        ['211', 'Terrains', 'active', 2],
        ['213', 'Constructions', 'active', 2],
        ['218', 'Matériel informatique', 'active', 2],
        ['281', 'Amortissements', 'active', 2],
        ['301', 'Stocks de marchandises', 'active', 3],
        ['401', 'Fournisseurs', 'passive', 4],
        ['411', 'Clients', 'active', 4],
        ['421', 'Personnel', 'passive', 4],
        ['431', 'Sécurité sociale', 'passive', 4],
        ['441', 'État (TVA)', 'passive', 4],
        ['471', "Comptes d'attente", 'active', 4],
        ['511', 'Banque', 'active', 5],
        ['521', 'Caisse', 'active', 5],
        ['581', 'Virements internes', 'active', 5],
        ['601', 'Achats de marchandises', 'charge', 6],
        ['611', 'Transports', 'charge', 6],
        ['621', 'Personnel extérieur', 'charge', 6],
        ['631', 'Impôts et taxes', 'charge', 6],
        ['641', 'Salaires', 'charge', 6],
        ['651', 'Frais bancaires', 'charge', 6],
        ['671', 'Charges exceptionnelles', 'charge', 6],
        ['681', 'Dotations amortissements', 'charge', 6],
        ['701', 'Ventes de marchandises', 'produit', 7],
        ['711', 'Prestations de services', 'produit', 7],
        ['721', 'Produits accessoires', 'produit', 7],
        ['771', 'Produits financiers', 'produit', 7],
        ['781', 'Reprises provisions', 'produit', 7],
    ];

    public function definition(): array
    {
        $account = $this->faker->unique()->randomElement(self::$syscohadaAccounts);
        return [
            'code' => $account[0],
            'name' => $account[1],
            'type' => $account[2],
            'syscohada_class' => (string) $account[3],
            'is_active' => true,
            'is_syscohada' => true,
        ];
    }

    public function ofCode(string $code): static
    {
        return $this->state(function () use ($code) {
            $found = collect(self::$syscohadaAccounts)->first(fn($a) => $a[0] === $code);
            return $found ? [
                'code' => $found[0],
                'name' => $found[1],
                'type' => $found[2],
                'syscohada_class' => (string) $found[3],
            ] : [];
        });
    }

    public function active(): static
    {
        return $this->state(fn() => ['type' => 'active', 'is_active' => true]);
    }

    public function passive(): static
    {
        return $this->state(fn() => ['type' => 'passive', 'is_active' => true]);
    }
}
