<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\RegleFiscale;
use App\Services\Fiscalite\RegleFiscaleService;
use Illuminate\Support\Str;

class RegleFiscaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed some basic rules for tests
        RegleFiscale::create([
            'id' => Str::uuid(),
            'code_pays' => 'BJ',
            'type_impot' => 'TVA',
            'taux' => 0.1800,
            'conditions' => null,
            'date_debut_validite' => '2000-01-01',
            'version' => 1,
            'statut' => 'active',
        ]);

        RegleFiscale::create([
            'id' => Str::uuid(),
            'code_pays' => 'BJ',
            'type_impot' => 'AIB',
            'taux' => 0.0100,
            'conditions' => ['centre_impots' => 'DGE_DME'],
            'date_debut_validite' => '2000-01-01',
            'version' => 1,
            'statut' => 'active',
        ]);

        RegleFiscale::create([
            'id' => Str::uuid(),
            'code_pays' => 'BJ',
            'type_impot' => 'AIB',
            'taux' => 0.0500,
            'conditions' => ['centre_impots' => 'CSI'],
            'date_debut_validite' => '2000-01-01',
            'version' => 1,
            'statut' => 'active',
        ]);
    }

    public function test_it_resolves_tva_default_rule()
    {
        $service = new RegleFiscaleService();
        $regle = $service->resoudre('BJ', 'TVA', [], now());
        
        $this->assertNotNull($regle);
        $this->assertEquals(0.18, $regle->taux);
    }

    public function test_it_resolves_aib_for_dge_client()
    {
        $service = new RegleFiscaleService();
        $regle = $service->resoudre('BJ', 'AIB', ['centre_impots' => 'DGE_DME'], now());
        
        $this->assertNotNull($regle);
        $this->assertEquals(0.01, $regle->taux);
    }

    public function test_it_resolves_aib_for_csi_client()
    {
        $service = new RegleFiscaleService();
        $regle = $service->resoudre('BJ', 'AIB', ['centre_impots' => 'CSI'], now());
        
        $this->assertNotNull($regle);
        $this->assertEquals(0.05, $regle->taux);
    }

    public function test_it_returns_null_for_unknown_aib_condition()
    {
        $service = new RegleFiscaleService();
        $regle = $service->resoudre('BJ', 'AIB', ['centre_impots' => 'INCONNU'], now());
        
        // S'il n'y a pas de règle générale AIB (sans conditions), ça doit retourner null
        $this->assertNull($regle);
    }
}
