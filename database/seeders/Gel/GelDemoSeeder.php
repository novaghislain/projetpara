<?php

namespace Database\Seeders\Gel;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GelDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a dummy Cabinet
        $cabinetId = DB::table('gel_cabinets')->insertGetId([
            'nom' => 'Cabinet Démo SARL',
            'slug' => 'cabinet-demo-sarl-' . Str::random(5),
            'email' => 'contact@cabinetdemo.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2a. Create a dummy GEL Client
        $gelClientId = DB::table('gel_clients')->insertGetId([
            'nom_entreprise' => 'Entreprise Cliente SA',
            'email' => 'client@exemple.com',
            'cabinet_id' => $cabinetId,
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2b. Create a dummy ERP Client
        $erpClientId = DB::table('clients')->insertGetId([
            'company_name' => 'Entreprise Cliente SA',
            'email' => 'client@exemple.com',
            'status' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // 3. Create a User (Accountant) and assign
        $userId = DB::table('users')->insertGetId([
            'name' => 'Jean Comptable',
            'email' => 'jean.comptable.' . Str::random(5) . '@cabinetdemo.com',
            'password' => bcrypt('password'),
            'cabinet_id' => $cabinetId,
            'client_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Create Partners
        $partnerId = DB::table('partners')->insertGetId([
            'client_id' => $erpClientId,
            'type' => 'customer',
            'company_name' => 'Acme Corp',
            'email' => 'billing@acmecorp.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Create a Product
        $productId = DB::table('products')->insertGetId([
            'client_id' => $erpClientId,
            'name' => 'Consulting Informatique',
            'description' => 'Heure de consulting en systèmes d\'information',
            'price_ht' => 50000,
            'price_ttc' => 59000,
            'tva_rate' => 18,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Create an Invoice
        $invoiceId = DB::table('invoices')->insertGetId([
            'client_id' => $erpClientId,
            'type' => 'customer_invoice',
            'invoice_number' => 'INV-' . date('Ym') . '-001',
            'partner_id' => $partnerId,
            'partner_name' => 'Acme Corp',
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'confirmed',
            'subtotal' => 150000,
            'tax_base' => 150000,
            'vat_total' => 27000, // 18%
            'total' => 177000,
            'balance_due' => 177000,
            'currency' => 'XAF',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Invoice Line
        DB::table('invoice_lines')->insert([
            'client_id' => $erpClientId,
            'invoice_id' => $invoiceId,
            'line_number' => 1,
            'description' => 'Consulting Informatique (3 Heures)',
            'quantity' => 3,
            'unit_price' => 50000,
            'subtotal' => 150000,
            'vat_rate' => 18,
            'total' => 177000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Create a Journal
        $journalId = DB::table('gel_journaux')->insertGetId([
            'cabinet_id' => $cabinetId,
            'code' => 'VT',
            'libelle' => 'Journal des Ventes',
            'type' => 'vente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 8. Create an Accounting Entry
        $ecritureId = DB::table('gel_ecritures')->insertGetId([
            'cabinet_id' => $cabinetId,
            'client_id' => $gelClientId,
            'journal_id' => $journalId,
            'numero' => 'ECR-DEMO-001',
            'date_ecriture' => now()->format('Y-m-d'),
            'libelle' => 'Facture client INV-001',
            'total_debit' => 177000,
            'total_credit' => 177000,
            'valide' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get Account IDs
        $clientAccount = DB::table('gel_comptes_comptables')->where('code', 'LIKE', '411%')->first()->id ?? 1;
        $salesAccount = DB::table('gel_comptes_comptables')->where('code', 'LIKE', '701%')->first()->id ?? 2;
        $vatAccount = DB::table('gel_comptes_comptables')->where('code', 'LIKE', '443%')->first()->id ?? 3;

        // Debit Line
        DB::table('gel_lignes_ecriture')->insert([
            'ecriture_id' => $ecritureId,
            'compte_id' => $clientAccount,
            'libelle_ligne' => 'Facture client INV-001',
            'sens' => 'debit',
            'montant' => 177000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Credit Line (Sales)
        DB::table('gel_lignes_ecriture')->insert([
            'ecriture_id' => $ecritureId,
            'compte_id' => $salesAccount,
            'libelle_ligne' => 'Facture client INV-001',
            'sens' => 'credit',
            'montant' => 150000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Credit Line (VAT)
        DB::table('gel_lignes_ecriture')->insert([
            'ecriture_id' => $ecritureId,
            'compte_id' => $vatAccount,
            'libelle_ligne' => 'Facture client INV-001',
            'sens' => 'credit',
            'montant' => 27000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 9. AI Suggestions
        DB::table('ai_suggestions')->insert([
            'client_id' => $erpClientId,
            'type' => 'invoice_classification',
            'agent' => 'Comptable IA',
            'title' => 'Compte de tiers manquant',
            'status' => 'pending',
            'description' => 'La facture INV-001 n\'a pas de compte de tiers associé.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('GEL Cabinet - Données de démo générées avec succès !');
    }
}
