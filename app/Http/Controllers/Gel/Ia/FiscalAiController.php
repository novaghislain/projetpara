<?php

namespace App\Http\Controllers\Gel\Ia;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\IA\FiscalAiService;
use Illuminate\Http\Request;

class FiscalAiController extends Controller
{
    protected FiscalAiService $fiscalAiService;

    public function __construct(FiscalAiService $fiscalAiService)
    {
        $this->fiscalAiService = $fiscalAiService;
    }

    /**
     * Analyse les données comptables et génère des suggestions (TVA, etc.).
     */
    public function generateSuggestions(Request $request)
    {
        $clientId = $request->user()->active_client_id;
        $client = Client::findOrFail($clientId);

        $this->fiscalAiService->generateTaxSuggestions($client);

        return response()->json([
            'success' => true,
            'message' => 'L\'Agent Fiscal a terminé son analyse et a généré de nouvelles suggestions.',
        ]);
    }
}
