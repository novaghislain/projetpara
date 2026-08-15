<?php

namespace App\Http\Controllers\Gel\Rh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rh\EmploymentContract;
use App\Services\Rh\ContractGenerationService;

class EmploymentContractController extends Controller
{
    protected ContractGenerationService $generator;

    public function __construct(ContractGenerationService $generator)
    {
        $this->generator = $generator;
    }

    public function createContract(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'employee_name' => 'required|string',
            'position' => 'required|string',
            'contract_type' => 'required|string',
            'start_date' => 'required|date',
            'gross_salary' => 'required|numeric'
        ]);

        $contract = EmploymentContract::create($request->all());

        // Générer le HTML initial
        $this->generator->generateHtml($contract);

        return response()->json([
            'status' => 'success',
            'data' => $contract,
            'message' => 'Contrat généré et prêt pour signature.'
        ]);
    }

    public function signContract(Request $request, $id)
    {
        $contract = EmploymentContract::findOrFail($id);

        $request->validate([
            'signature_base64' => 'required|string'
        ]);

        $contract->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signature_path' => $request->signature_base64
        ]);

        // Régénérer le HTML pour incruster la signature
        $this->generator->generateHtml($contract);

        return response()->json([
            'status' => 'success',
            'message' => 'Le contrat a été signé électroniquement avec succès.'
        ]);
    }
}
