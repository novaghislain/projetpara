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
     * Store a new employee.
     *
     * POST /erp/hr/employees
     */
    public function storeEmployee(Request $request)
    {
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

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
     * Generate (store) a payroll for an employee.
     *
     * POST /erp/hr/payrolls
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $data['bonuses']    = $data['bonuses'] ?? 0;
        $data['deductions'] = $data['deductions'] ?? 0;
        $data['advances']   = $data['advances'] ?? 0;

        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        $payroll = ErpPayroll::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Payroll generated successfully.',
            'data'    => $payroll->load('employee'),
        ], 201);
    }
}
