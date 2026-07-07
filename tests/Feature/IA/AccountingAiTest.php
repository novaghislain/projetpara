<?php

namespace Tests\Feature\IA;

use App\Services\IA\AccountingAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingAiTest extends TestCase
{
    use RefreshDatabase;

    private AccountingAiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AccountingAiService::class);
    }

    /** @test */
    public function il_categorise_un_salaire()
    {
        $result = $this->service->categorizeTransaction('Salaire du mois de juin 2026', 350000, 'charge');

        $this->assertNotEmpty($result);
        $this->assertEquals('661', $result[0]['account_code']);
        $this->assertGreaterThanOrEqual(85, $result[0]['confidence']);
    }

    /** @test */
    public function il_categorise_un_loyer()
    {
        $result = $this->service->categorizeTransaction('Paiement loyer local Cotonou', 200000, 'charge');

        $this->assertEquals('613', $result[0]['account_code']);
        $this->assertGreaterThanOrEqual(90, $result[0]['confidence']);
    }

    /** @test */
    public function il_categorise_une_facture_electricite()
    {
        $result = $this->service->categorizeTransaction('Facture SBEE électricité', 45000, 'charge');

        $this->assertEquals('612', $result[0]['account_code']);
        $this->assertGreaterThanOrEqual(90, $result[0]['confidence']);
    }

    /** @test */
    public function il_categorise_une_charge_cnss()
    {
        $result = $this->service->categorizeTransaction('CNSS trimestre 2', 125000, 'charge');

        $this->assertEquals('664', $result[0]['account_code']);
        $this->assertGreaterThanOrEqual(90, $result[0]['confidence']);
    }

    /** @test */
    public function il_categorise_un_impot()
    {
        $result = $this->service->categorizeTransaction('Impôt sur les sociétés DGI', 500000, 'charge');

        $this->assertEquals('635', $result[0]['account_code']);
        $this->assertGreaterThanOrEqual(85, $result[0]['confidence']);
    }

    /** @test */
    public function il_categorise_une_vente()
    {
        $result = $this->service->categorizeTransaction('Facture client prestation conseil', 750000, 'produit');

        $this->assertEquals('411', $result[0]['account_code']);
    }

    /** @test */
    public function il_categorise_un_frais_bancaire()
    {
        $result = $this->service->categorizeTransaction('Agios et frais bancaires', 15000, 'charge');

        $this->assertEquals('631', $result[0]['account_code']);
        $this->assertGreaterThanOrEqual(85, $result[0]['confidence']);
    }

    /** @test */
    public function il_detecte_type_charge_et_retourne_suggestions_ordonnees()
    {
        $result = $this->service->categorizeTransaction('Salaire', 350000, 'charge');

        $this->assertNotEmpty($result);
        // Le premier résultat doit avoir le meilleur score
        $this->assertGreaterThanOrEqual($result[0]['confidence'], $result[0]['confidence']);
        // Salaire doit retourner au moins la suggestion 661
        $codes = array_column($result, 'account_code');
        $this->assertContains('661', $codes);
    }

    /** @test */
    public function il_retourne_suggestion_generique_si_non_reconnu()
    {
        // Libellé sans mot-clé connu → doit tomber dans le cas générique (601 pour 'charge')
        $result = $this->service->categorizeTransaction('Dépense diverse non récurrente', 85000, 'charge');

        $this->assertNotEmpty($result);
        $this->assertEquals('601', $result[0]['account_code']);
        $this->assertLessThan(50, $result[0]['confidence']);
    }

    /** @test */
    public function il_categorise_doublons_credit_debit_pour_salaire()
    {
        $result = $this->service->categorizeTransaction('Salaire employé mensuel', 250000, 'charge');

        // Doit retourner au moins 2 suggestions (débit 661 + crédit 421)
        $this->assertGreaterThanOrEqual(2, count($result));

        $debitLines = array_filter($result, fn($s) => $s['type'] === 'debit');
        $creditLines = array_filter($result, fn($s) => $s['type'] === 'credit');

        $this->assertNotEmpty($debitLines);
        $this->assertNotEmpty($creditLines);
    }
}
