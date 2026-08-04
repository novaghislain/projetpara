<?php

namespace App\Http\Controllers\Gel\Erp;

use App\Http\Controllers\Controller;
use App\Models\ErpEmployee;
use App\Models\ErpPayroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Contrôleur de gestion des employés et de la paie dans le module ERP.
     * Permet de créer des employés et de générer leurs bulletins de paie.
     */
    public function storeEmployee(Request $request)
    {
        /**
         * Crée un nouvel employé dans le système ERP.
         *
         * POST /erp/hr/employees
         *
         * @param Request $request La requête HTTP contenant les données de l'employé
         * @return \Illuminate\Http\JsonResponse La réponse JSON avec l'employé créé
         */
        $validator = Validator::make($request->all(), [
            'matricule'   => 'required|string|max:50|unique:erp_employees,matricule',
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'position'    => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'base_salary' => 'required|numeric|min:0',
            'cnss_number' => 'nullable|string|max:100',
            'ifu_number'  => 'nullable|string|max:100',
            'hire_date'   => 'required|date',
            'status'      => 'nullable|string|in:active,inactive',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Valeur par défaut du statut : actif
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        $employee = ErpEmployee::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully.',
            'data'    => $employee,
        ], 201);
    }

    /**
     * Génère une fiche de paie pour un employé.
     *
     * POST /erp/hr/payrolls
     *
     * @param Request $request La requête HTTP contenant les données de la paie
     * @return \Illuminate\Http\JsonResponse La réponse JSON avec la paie générée
     */
    public function generatePayroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'erp_employee_id' => 'required|integer|exists:erp_employees,id',
            'period'          => 'required|string|max:7',
            'base_salary'     => 'required|numeric|min:0',
            'bonuses'         => 'nullable|numeric|min:0',
            'deductions'      => 'nullable|numeric|min:0',
            'advances'        => 'nullable|numeric|min:0',
            'net_salary'      => 'required|numeric|min:0',
            'status'          => 'nullable|string|in:pending,paid,cancelled',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Initialisation des champs optionnels à zéro par défaut
        $data['bonuses']    = $data['bonuses'] ?? 0;
        $data['deductions'] = $data['deductions'] ?? 0;
        $data['advances']   = $data['advances'] ?? 0;

        // Statut par défaut : en attente
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        // Création de la fiche de paie avec chargement de l'employé associé
        $payroll = ErpPayroll::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Payroll generated successfully.',
            'data'    => $payroll->load('employee'),
        ], 201);
    }
}
