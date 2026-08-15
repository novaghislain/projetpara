<?php

namespace App\Http\Controllers\Gel\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\Asset;
use App\Services\Accounting\AssetService;

class AssetController extends Controller
{
    protected AssetService $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function generateDepreciationTable(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'name' => 'required|string',
            'purchase_date' => 'required|date',
            'purchase_price' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'salvage_value' => 'nullable|numeric|min:0',
        ]);

        // Simuler la création pour le tableau ou enregistrer directement
        $asset = Asset::create([
            'client_id' => $request->client_id,
            'name' => $request->name,
            'purchase_date' => $request->purchase_date,
            'purchase_price' => $request->purchase_price,
            'useful_life_years' => $request->useful_life_years,
            'salvage_value' => $request->salvage_value ?? 0,
            'depreciation_method' => 'linear',
            'status' => 'active',
        ]);

        $depreciations = $this->assetService->generateLinearDepreciation($asset);

        return response()->json([
            'status' => 'success',
            'data' => [
                'asset' => $asset,
                'depreciation_table' => $depreciations
            ],
            'message' => 'Tableau d\'amortissement généré avec succès.'
        ]);
    }
}
