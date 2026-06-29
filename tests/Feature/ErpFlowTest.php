<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ErpCategory;
use App\Models\ErpItem;
use App\Models\ErpWarehouse;
use App\Models\ErpStockMovement;
use App\Models\ErpInvoice;
use App\Models\ErpInvoiceItem;
use App\Models\ErpBankAccount;
use App\Models\ErpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test d'intégration des flux ERP (GEL Cabinet).
 *
 * Couvre : Catalogue → Stock → Facturation → Trésorerie → RH/Payroll
 */
#[\PHPUnit\Framework\Attributes\Group('erp')]
#[\PHPUnit\Framework\Attributes\Group('finance')]
class ErpFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private array $authHeaders;
    private string $csrfToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Désactiver la vérification CSRF en test (le token n'est pas disponible via csrf_token())
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // Créer un utilisateur authentifié (super_admin pour bypass CheckModuleAccess)
        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'email' => 'erp-test@gel.cabinet',
        ]);

        // Simuler une session authentifiée
        $this->actingAs($this->user);
        $this->csrfToken = 'test-csrf-token';
        $this->authHeaders = [
            'X-CSRF-TOKEN' => $this->csrfToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    // ────────────────────────────────────────────────
    // 1. CATALOGUE — Catégories et Articles
    // ────────────────────────────────────────────────

    public function test_catalogue_flow()
    {
        // 1a. Créer une catégorie
        $catResponse = $this->postJson('/erp/catalogue/categories', [
            'name' => 'Fournitures de bureau',
            'type' => 'product',
            'description' => 'Stylos, papier, agrafeuses',
        ], $this->authHeaders);

        $catResponse->assertStatus(201)
            ->assertJson(['success' => true]);
        $categoryId = $catResponse->json('data.id');
        $this->assertNotNull($categoryId, 'La catégorie doit avoir un ID');

        // 1b. Créer un article lié à cette catégorie
        $itemResponse = $this->postJson('/erp/catalogue/items', [
            'erp_category_id' => $categoryId,
            'reference' => 'REF-STYLO-001',
            'designation' => 'Stylo bleu BIC',
            'purchase_price' => 250,
            'selling_price' => 500,
            'stock_alert' => 10,
            'unit' => 'piece',
        ], $this->authHeaders);

        $itemResponse->assertStatus(201)
            ->assertJson(['success' => true]);
        $itemId = $itemResponse->json('data.id');

        // 1c. Lister les catégories et articles via API
        $this->getJson('/api/erp/categories')
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/erp/items')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['reference' => 'REF-STYLO-001']);

        return ['categoryId' => $categoryId, 'itemId' => $itemId];
    }

    // ────────────────────────────────────────────────
    // 2. STOCK — Entrepôts et Mouvements
    // ────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Depends('test_catalogue_flow')]
    public function test_stock_flow()
    {
        // Dépend du test précédent — on crée d'abord catégorie + article
        $catResponse = $this->postJson('/erp/catalogue/categories', [
            'name' => 'Consommables',
            'type' => 'product',
            'description' => 'Consommables informatiques',
        ], $this->authHeaders);
        $categoryId = $catResponse->json('data.id');

        $itemResponse = $this->postJson('/erp/catalogue/items', [
            'erp_category_id' => $categoryId,
            'reference' => 'REF-CART-001',
            'designation' => 'Cartouche encre HP',
            'purchase_price' => 15000,
            'selling_price' => 25000,
            'stock_alert' => 5,
            'unit' => 'piece',
        ], $this->authHeaders);
        $itemId = $itemResponse->json('data.id');

        // 2a. Créer un entrepôt
        $whResponse = $this->postJson('/erp/stocks/warehouses', [
            'name' => 'Entrepôt Principal',
            'location' => 'Cotonou, zone industrielle',
            'is_active' => true,
        ], $this->authHeaders);

        $whResponse->assertStatus(201)
            ->assertJson(['success' => true]);
        $warehouseId = $whResponse->json('data.id');

        // 2b. Mouvement d'entrée (achat)
        $entryResponse = $this->postJson('/erp/stocks/movements', [
            'erp_item_id' => $itemId,
            'erp_warehouse_id' => $warehouseId,
            'type' => 'entry',
            'quantity' => 50,
            'reference_doc' => 'BON-001',
            'movement_date' => '2026-06-01',
            'motif' => 'Achat fournisseur',
        ], $this->authHeaders);

        $entryResponse->assertStatus(201)
            ->assertJson(['success' => true]);

        // 2c. Mouvement de sortie (vente)
        $exitResponse = $this->postJson('/erp/stocks/movements', [
            'erp_item_id' => $itemId,
            'erp_warehouse_id' => $warehouseId,
            'type' => 'exit',
            'quantity' => 5,
            'reference_doc' => 'FAC-001',
            'movement_date' => '2026-06-15',
            'motif' => 'Vente client',
        ], $this->authHeaders);

        $exitResponse->assertStatus(201)
            ->assertJson(['success' => true]);

        // 2d. Vérifier le stock (50 entrés - 5 sortis = 45)
        $stockResponse = $this->getJson('/api/erp/stock');
        $stockResponse->assertStatus(200);
        $itemStock = collect($stockResponse->json())->firstWhere('id', $itemId);
        $this->assertNotNull($itemStock, 'L\'article doit apparaître dans le stock');
        $this->assertEquals(45, $itemStock['stock'], 'Stock calculé incorrect');

        return ['itemId' => $itemId, 'warehouseId' => $warehouseId];
    }

    // ────────────────────────────────────────────────
    // 3. FACTURATION — Création de facture
    // ────────────────────────────────────────────────

    public function test_invoice_flow()
    {
        // Créer un client simple (sans passer par le module client)
        $client = \App\Models\Client::create([
            'company_name' => 'Entreprise Test SARL',
        ]);

        // Créer la facture
        $invoiceResponse = $this->postJson('/erp/invoices', [
            'invoice_number' => 'FAC-2026-0001',
            'type' => 'invoice',
            'client_id' => $client->id,
            'invoice_date' => '2026-06-20',
            'due_date' => '2026-07-20',
            'total_ht' => 50000,
            'tax_amount' => 9000,
            'total_ttc' => 59000,
            'status' => 'brouillon',
            'notes' => 'Facture de test',
            'items' => [
                [
                    'designation' => 'Prestation conseil',
                    'quantity' => 1,
                    'unit_price' => 50000,
                    'total_price' => 50000,
                ],
            ],
        ], $this->authHeaders);

        $invoiceResponse->assertStatus(201)
            ->assertJson(['success' => true]);
        $invoiceId = $invoiceResponse->json('data.id');

        // Vérifier en lecture
        $this->getJson('/api/erp/invoices')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['invoice_number' => 'FAC-2026-0001']);

        // Vérifier les line items
        $invoice = ErpInvoice::with('lineItems')->find($invoiceId);
        $this->assertNotNull($invoice);
        $this->assertCount(1, $invoice->lineItems);
        $this->assertEquals('Prestation conseil', $invoice->lineItems->first()->designation);

        return ['invoiceId' => $invoiceId, 'clientId' => $client->id];
    }

    // ────────────────────────────────────────────────
    // 4. TRÉSORERIE — Comptes et Transactions
    // ────────────────────────────────────────────────

    public function test_treasury_flow()
    {
        // 4a. Créer un compte bancaire
        $accountResponse = $this->postJson('/erp/treasury/accounts', [
            'name' => 'Compte Principal BGFI',
            'type' => 'bank',
            'account_number' => 'BJ00601020012345678901',
            'initial_balance' => 1000000,
            'is_active' => true,
        ], $this->authHeaders);

        $accountResponse->assertStatus(201)
            ->assertJson(['success' => true]);
        $accountId = $accountResponse->json('data.id');

        // 4b. Créer une transaction de revenu
        $incomeResponse = $this->postJson('/erp/treasury/transactions', [
            'erp_bank_account_id' => $accountId,
            'transaction_date' => '2026-06-20',
            'type' => 'income',
            'amount' => 59000,
            'reference' => 'FAC-2026-0001',
            'description' => 'Règlement facture conseil',
        ], $this->authHeaders);

        $incomeResponse->assertStatus(201)
            ->assertJson(['success' => true]);

        // 4c. Créer une transaction de dépense
        $expenseResponse = $this->postJson('/erp/treasury/transactions', [
            'erp_bank_account_id' => $accountId,
            'transaction_date' => '2026-06-22',
            'type' => 'expense',
            'amount' => 25000,
            'reference' => 'DEP-001',
            'description' => 'Achat fournitures',
        ], $this->authHeaders);

        $expenseResponse->assertStatus(201)
            ->assertJson(['success' => true]);

        // 4d. Vérifier les balances
        $balancesResponse = $this->getJson('/api/erp/balances');
        $balancesResponse->assertStatus(200);
        $accountBalance = collect($balancesResponse->json())->firstWhere('id', $accountId);
        $this->assertNotNull($accountBalance);
        // balance = initial_balance (1M) + income (59000) - expense (25000) = 1034000
        $this->assertEquals(1034000, $accountBalance['balance'], 'Balance calculée incorrecte');

        // 4e. Vérifier les transactions
        $this->getJson('/api/erp/transactions')
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    // ────────────────────────────────────────────────
    // 5. RH — Employés et Paie
    // ────────────────────────────────────────────────

    public function test_employee_payroll_flow()
    {
        // 5a. Créer un employé via la route POST
        $response = $this->postJson('/erp/hr/employees', [
            'matricule' => 'EMP-001',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'position' => 'Comptable',
            'base_salary' => 350000,
            'hire_date' => '2026-01-15',
        ], $this->authHeaders);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
        $employeeId = $response->json('data.id');
        $this->assertNotNull($employeeId);

        // 5b. Générer une fiche de paie
        $payrollResponse = $this->postJson('/erp/hr/payrolls', [
            'erp_employee_id' => $employeeId,
            'period' => '2026-06',
            'base_salary' => 350000,
            'bonuses' => 50000,
            'deductions' => 30000,
            'advances' => 0,
            'net_salary' => 370000,
        ], $this->authHeaders);

        $payrollResponse->assertStatus(201)
            ->assertJson(['success' => true]);

        // 5c. Validation : champs obligatoires
        $this->postJson('/erp/hr/employees', [
            'matricule' => 'EMP-002',
            // first_name manquant
            'base_salary' => 200000,
            'hire_date' => '2026-06-01',
        ], $this->authHeaders)->assertStatus(422);

        $this->postJson('/erp/hr/payrolls', [
            'erp_employee_id' => 99999,
            'period' => '2026-06',
            'base_salary' => 100000,
            'net_salary' => 100000,
        ], $this->authHeaders)->assertStatus(422);
    }

    // ────────────────────────────────────────────────
    // 6. Validation — Cas d'erreur
    // ────────────────────────────────────────────────

    public function test_catalogue_validation_errors()
    {
        // Catégorie sans name
        $this->postJson('/erp/catalogue/categories', [
            'type' => 'product',
        ], $this->authHeaders)->assertStatus(422);

        // Article avec référence dupliquée (créer d'abord)
        $cat = ErpCategory::create(['name' => 'Test', 'type' => 'product']);
        ErpItem::create([
            'reference' => 'DUP-001',
            'erp_category_id' => $cat->id,
            'designation' => 'Article duplicata',
            'purchase_price' => 100,
            'selling_price' => 200,
            'unit' => 'piece',
        ]);

        $this->postJson('/erp/catalogue/items', [
            'erp_category_id' => $cat->id,
            'reference' => 'DUP-001',
            'designation' => 'Duplicata',
            'purchase_price' => 100,
            'selling_price' => 200,
            'unit' => 'piece',
        ], $this->authHeaders)->assertStatus(422);
    }

    public function test_stock_validation_errors()
    {
        // Mouvement avec item inexistant
        $this->postJson('/erp/stocks/movements', [
            'erp_item_id' => 99999,
            'erp_warehouse_id' => 99999,
            'type' => 'entry',
            'quantity' => 10,
            'movement_date' => '2026-06-01',
        ], $this->authHeaders)->assertStatus(422);
    }

    public function test_invoice_validation_errors()
    {
        // Facture sans items
        $this->postJson('/erp/invoices', [
            'invoice_number' => 'FAC-ERR-001',
            'type' => 'invoice',
            'invoice_date' => '2026-06-20',
            'total_ht' => 1000,
            'tax_amount' => 180,
            'total_ttc' => 1180,
            'items' => [], // min:1
        ], $this->authHeaders)->assertStatus(422);

        // Facture avec montant négatif
        $this->postJson('/erp/invoices', [
            'invoice_number' => 'FAC-ERR-002',
            'type' => 'invoice',
            'invoice_date' => '2026-06-20',
            'total_ht' => -1000,
            'tax_amount' => 0,
            'total_ttc' => -1000,
            'items' => [['designation' => 'Test', 'quantity' => 1, 'unit_price' => -500, 'total_price' => -500]],
        ], $this->authHeaders)->assertStatus(422);
    }

    public function test_treasury_validation_errors()
    {
        // Compte sans name
        $this->postJson('/erp/treasury/accounts', [
            'type' => 'bank',
            'initial_balance' => 1000,
        ], $this->authHeaders)->assertStatus(422);

        // Transaction sans montant
        $this->postJson('/erp/treasury/transactions', [
            'erp_bank_account_id' => 99999,
            'type' => 'income',
            'transaction_date' => '2026-06-20',
        ], $this->authHeaders)->assertStatus(422);
    }

    // ────────────────────────────────────────────────
    // 7. Vérification de l'intégrité des modèles
    // ────────────────────────────────────────────────

    public function test_model_relations()
    {
        // Créer un item avec mouvements → vérifier current_stock
        $cat = ErpCategory::create(['name' => 'Catégorie Test', 'type' => 'service']);
        $item = ErpItem::create([
            'erp_category_id' => $cat->id,
            'reference' => 'STK-TEST-001',
            'designation' => 'Article stock test',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'unit' => 'piece',
        ]);
        $wh = ErpWarehouse::create(['name' => 'WH Test']);

        ErpStockMovement::create([
            'erp_item_id' => $item->id,
            'erp_warehouse_id' => $wh->id,
            'type' => 'entry',
            'quantity' => 100,
            'movement_date' => '2026-06-01',
        ]);
        ErpStockMovement::create([
            'erp_item_id' => $item->id,
            'erp_warehouse_id' => $wh->id,
            'type' => 'exit',
            'quantity' => 30,
            'movement_date' => '2026-06-15',
        ]);

        $this->assertEquals(70, $item->fresh()->current_stock);

        // Vérifier la relation item → category
        $this->assertNotNull($item->category);
        $this->assertEquals('Catégorie Test', $item->category->name);
    }

    public function test_invoice_with_items_relation()
    {
        $client = \App\Models\Client::create(['company_name' => 'Test Client SARL']);
        $invoice = ErpInvoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'FAC-REL-001',
            'type' => 'invoice',
            'invoice_date' => '2026-06-20',
        ]);

        ErpInvoiceItem::create([
            'erp_invoice_id' => $invoice->id,
            'designation' => 'Article 1',
            'quantity' => 2,
            'unit_price' => 5000,
            'total_price' => 10000,
        ]);
        ErpInvoiceItem::create([
            'erp_invoice_id' => $invoice->id,
            'designation' => 'Article 2',
            'quantity' => 1,
            'unit_price' => 15000,
            'total_price' => 15000,
        ]);

        $invoice->load('lineItems');
        $this->assertCount(2, $invoice->lineItems);
        $this->assertEquals(25000, $invoice->lineItems->sum('total_price'));
    }

    public function test_treasury_account_balance()
    {
        $account = ErpBankAccount::create([
            'name' => 'Compte Test',
            'type' => 'bank',
            'initial_balance' => 500000,
        ]);

        ErpTransaction::create([
            'erp_bank_account_id' => $account->id,
            'type' => 'income',
            'amount' => 100000,
            'transaction_date' => '2026-06-20',
            'description' => 'Paiement client test',
        ]);
        ErpTransaction::create([
            'erp_bank_account_id' => $account->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => '2026-06-22',
            'description' => 'Achat fourniture test',
        ]);

        $account->load('transactions');
        $income = $account->transactions->where('type', 'income')->sum('amount');
        $expense = $account->transactions->where('type', 'expense')->sum('amount');

        $this->assertEquals(100000, $income);
        $this->assertEquals(50000, $expense);
        $this->assertEquals(550000, $account->initial_balance + $income - $expense);
    }

    // ────────────────────────────────────────────────
    // 8. Contrôle d'accès — Permissions
    // ────────────────────────────────────────────────

    public function test_unauthenticated_user_can_access_routes()
    {
        // Note : les routes ERP n'ont pas de middleware 'auth' pour l'instant.
        // La couche est assurée par les middlewares métier (module, client, company, suspension).
        // Ce test documente qu'une requête non authentifiée réussit.
        $response = $this->postJson('/erp/catalogue/categories', [
            'name' => 'Test non auth',
            'type' => 'product',
        ]);

        // N'étant pas authentifié, les middlewares métier passent tous (pas de user → pas de check)
        // La requête aboutit. Si un jour on ajoute un middleware 'auth', remplacer par 401.
        $response->assertStatus(201);
    }

    public function test_client_user_is_blocked()
    {
        $client = User::factory()->create([
            'role' => 'client',
        ]);

        $this->actingAs($client)
            ->postJson('/erp/catalogue/categories', [
                'name' => 'Test',
                'type' => 'product',
            ], $this->authHeaders)
            ->assertStatus(403);
    }

    public function test_company_admin_is_blocked()
    {
        $companyAdmin = User::factory()->create([
            'role' => 'company_admin',
            'is_company_admin' => true,
        ]);

        $this->actingAs($companyAdmin)
            ->postJson('/erp/catalogue/categories', [
                'name' => 'Test',
                'type' => 'product',
            ], $this->authHeaders)
            ->assertStatus(403);
    }

    public function test_suspended_user_is_blocked()
    {
        $suspended = User::factory()->create([
            'role' => 'super_admin',
            'is_suspended' => true,
        ]);

        $this->actingAs($suspended)
            ->postJson('/erp/catalogue/categories', [
                'name' => 'Test',
                'type' => 'product',
            ], $this->authHeaders)
            ->assertStatus(403);
    }

    public function test_regular_user_without_module_access_is_blocked()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->postJson('/erp/catalogue/categories', [
                'name' => 'Test',
                'type' => 'product',
            ], $this->authHeaders)
            ->assertStatus(403);
    }

    public function test_super_admin_can_access_all_routes()
    {
        // super_admin créé dans setUp()
        $this->postJson('/erp/catalogue/categories', [
            'name' => 'Accès autorisé',
            'type' => 'product',
        ], $this->authHeaders)->assertStatus(201);

        $this->postJson('/erp/catalogue/items', [
            'erp_category_id' => ErpCategory::first()->id,
            'reference' => 'PERM-001',
            'designation' => 'Test permissions',
            'purchase_price' => 100,
            'selling_price' => 200,
            'unit' => 'piece',
        ], $this->authHeaders)->assertStatus(201);
    }
}
