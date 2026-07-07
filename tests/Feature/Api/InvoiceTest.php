<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('accounting')]
#[\PHPUnit\Framework\Attributes\Group('api')]
#[\PHPUnit\Framework\Attributes\Group('invoicing')]
class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private Partner $partner;
    private array $authHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'company_name' => 'Client Test Facturation SARL',
            'email' => 'facture-test@example.com',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'email' => 'invoice-test@gel.cabinet',
            'client_id' => $this->client->id,
            'active_client_id' => $this->client->id,
        ]);

        $this->partner = Partner::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'customer',
            'company_name' => 'Client Partenaire SARL',
            'email' => 'partenaire@example.com',
        ]);

        $this->actingAs($this->user);
        $this->authHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function test_index_returns_invoices(): void
    {
        Invoice::factory()->count(3)->create([
            'client_id' => $this->client->id,
            'type' => 'customer_invoice',
        ]);

        $response = $this->getJson('/api/invoices', $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_store_creates_invoice(): void
    {
        $response = $this->postJson('/api/invoices', [
            'type' => 'customer_invoice',
            'partner_id' => $this->partner->id,
            'invoice_date' => '2026-06-20',
            'due_date' => '2026-07-20',
            'currency' => 'XOF',
            'lines' => [
                [
                    'description' => 'Prestation conseil',
                    'quantity' => 1,
                    'unit_price' => 100000,
                    'vat_rate' => 18,
                ],
            ],
        ], $this->authHeaders);

        $response->assertStatus(201);
    }

    public function test_show_returns_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'customer_invoice',
            'invoice_number' => 'FACT-2026-0050',
        ]);

        $response = $this->getJson("/api/invoices/{$invoice->id}", $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonFragment(['invoice_number' => 'FACT-2026-0050']);
    }

    public function test_filter_invoices_by_status(): void
    {
        Invoice::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'pending',
            'type' => 'customer_invoice',
        ]);
        Invoice::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'paid',
            'type' => 'customer_invoice',
        ]);

        $response = $this->getJson('/api/invoices?status=paid', $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }
}
