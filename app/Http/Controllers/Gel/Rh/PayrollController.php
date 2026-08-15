<?php

namespace App\Http\Controllers\Gel\Rh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Hr\PayrollEngineService;

class PayrollController extends Controller
{
    protected PayrollEngineService $payrollEngine;

    public function __construct(PayrollEngineService $payrollEngine)
    {
        $this->payrollEngine = $payrollEngine;
    }

    /**
     * Génère une simulation de fiche de paie (Bénin)
     */
    public function simulatePayslip(Request $request)
    {
        $request->validate([
            'gross_salary' => 'required|numeric|min:52000', // SMIG Bénin: 52 000 FCFA
            'children_count' => 'nullable|integer|min:0'
        ]);

        $grossSalary = (float) $request->gross_salary;
        $childrenCount = (int) $request->get('children_count', 0);

        $payslip = $this->payrollEngine->generatePayslip($grossSalary, $childrenCount);

        return response()->json([
            'status' => 'success',
            'data' => $payslip,
            'message' => 'Fiche de paie générée avec succès.'
        ]);
    }
}
