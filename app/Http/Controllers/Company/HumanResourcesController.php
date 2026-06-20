<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyEmployee;
use App\Models\CompanyLeaveRequest;
use App\Models\CompanyExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HumanResourcesController extends Controller
{
    /**
     * Récupère le client_id de l'utilisateur authentifié.
     */
    private function getClientId()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            abort(403, 'Aucune entreprise associée.');
        }
        return $user->client_id;
    }

    /**
     * Affiche la page RH.
     */
    public function index()
    {
        $clientId = $this->getClientId();
        return view('company', ['page' => 'company-hr', 'clientId' => $clientId]);
    }

    // ─── EMPLOYÉS ─────────────────────────────────────────────────────────────

    /**
     * API: Liste tous les employés de l'entreprise.
     */
    public function employees()
    {
        $clientId = $this->getClientId();

        $employees = CompanyEmployee::byClient($clientId)
            ->withCount('leaveRequests', 'expenses')
            ->latest()
            ->get()
            ->map(function ($emp) {
                return [
                    'id'            => $emp->id,
                    'first_name'    => $emp->first_name,
                    'last_name'     => $emp->last_name,
                    'email'         => $emp->email,
                    'phone'         => $emp->phone,
                    'position'      => $emp->position,
                    'department'    => $emp->department,
                    'hire_date'     => $emp->hire_date?->format('Y-m-d'),
                    'salary'        => (float) $emp->salary,
                    'contract_type' => $emp->contract_type,
                    'status'        => $emp->status,
                    'leave_requests_count' => $emp->leave_requests_count,
                    'expenses_count'       => $emp->expenses_count,
                    'created_at'    => $emp->created_at?->format('d/m/Y'),
                ];
            });

        return response()->json($employees);
    }

    /**
     * API: Crée un employé.
     */
    public function storeEmployee(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'nullable|string|max:50',
            'position'      => 'required|string|max:255',
            'department'    => 'required|string|max:255',
            'hire_date'     => 'required|date',
            'salary'        => 'nullable|numeric|min:0',
            'contract_type' => 'required|in:CDI,CDD,INTERIM,STAGE',
            'status'        => 'required|in:active,suspended,left',
        ]);

        $employee = CompanyEmployee::create(array_merge(
            $validated,
            ['client_id' => $clientId]
        ));

        return response()->json([
            'message'  => 'Employé créé avec succès.',
            'employee' => $employee->fresh(),
        ], 201);
    }

    /**
     * API: Modifie un employé.
     */
    public function updateEmployee(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $employee = CompanyEmployee::byClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'first_name'    => 'sometimes|string|max:255',
            'last_name'     => 'sometimes|string|max:255',
            'email'         => 'sometimes|email|max:255',
            'phone'         => 'sometimes|nullable|string|max:50',
            'position'      => 'sometimes|string|max:255',
            'department'    => 'sometimes|string|max:255',
            'hire_date'     => 'sometimes|date',
            'salary'        => 'sometimes|nullable|numeric|min:0',
            'contract_type' => 'sometimes|in:CDI,CDD,INTERIM,STAGE',
            'status'        => 'sometimes|in:active,suspended,left',
        ]);

        $employee->update($validated);

        return response()->json([
            'message'  => 'Employé mis à jour.',
            'employee' => $employee->fresh(),
        ]);
    }

    /**
     * API: Supprime un employé.
     */
    public function destroyEmployee($id)
    {
        $clientId = $this->getClientId();

        $employee = CompanyEmployee::byClient($clientId)->findOrFail($id);
        $employee->delete();

        return response()->json(['message' => 'Employé supprimé.']);
    }

    // ─── CONGÉS ───────────────────────────────────────────────────────────────

    /**
     * API: Liste toutes les demandes de congés.
     */
    public function leaveRequests()
    {
        $clientId = $this->getClientId();

        $leaveRequests = CompanyLeaveRequest::whereHas('employee', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })
        ->with(['employee:id,first_name,last_name,department', 'approver:id,name'])
        ->latest()
        ->get()
        ->map(function ($lr) {
            return [
                'id'             => $lr->id,
                'employee_id'    => $lr->employee_id,
                'employee_name'  => $lr->employee?->first_name . ' ' . $lr->employee?->last_name,
                'department'     => $lr->employee?->department,
                'type'           => $lr->type,
                'start_date'     => $lr->start_date?->format('Y-m-d'),
                'end_date'       => $lr->end_date?->format('Y-m-d'),
                'reason'         => $lr->reason,
                'status'         => $lr->status,
                'approved_by'    => $lr->approver?->name,
                'approver_notes' => $lr->approver_notes,
                'created_at'     => $lr->created_at?->format('d/m/Y'),
            ];
        });

        return response()->json($leaveRequests);
    }

    /**
     * API: Crée une demande de congé.
     */
    public function storeLeaveRequest(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'employee_id' => 'required|exists:company_employees,id',
            'type'        => 'required|in:conge,maladie,autre',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string',
        ]);

        // Vérifier que l'employé appartient bien au client
        $employee = CompanyEmployee::byClient($clientId)->findOrFail($validated['employee_id']);

        $leaveRequest = CompanyLeaveRequest::create($validated);

        return response()->json([
            'message' => 'Demande de congé créée.',
            'leave_request' => $leaveRequest->fresh(['employee:id,first_name,last_name']),
        ], 201);
    }

    /**
     * API: Approuve ou rejette une demande de congé.
     */
    public function approveLeave(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $leaveRequest = CompanyLeaveRequest::whereHas('employee', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })->findOrFail($id);

        $validated = $request->validate([
            'status'         => 'required|in:approved,rejected',
            'approver_notes' => 'nullable|string',
        ]);

        $leaveRequest->update([
            'status'         => $validated['status'],
            'approved_by'    => Auth::id(),
            'approver_notes' => $validated['approver_notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Demande de congé ' . ($validated['status'] === 'approved' ? 'approuvée' : 'rejetée') . '.',
            'leave_request' => $leaveRequest->fresh(['employee:id,first_name,last_name', 'approver:id,name']),
        ]);
    }

    // ─── NOTES DE FRAIS ───────────────────────────────────────────────────────

    /**
     * API: Liste les notes de frais.
     */
    public function expenses()
    {
        $clientId = $this->getClientId();

        $expenses = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })
        ->with(['employee:id,first_name,last_name,department', 'approver:id,name'])
        ->latest()
        ->get()
        ->map(function ($exp) {
            return [
                'id'            => $exp->id,
                'employee_id'   => $exp->employee_id,
                'employee_name' => $exp->employee?->first_name . ' ' . $exp->employee?->last_name,
                'department'    => $exp->employee?->department,
                'category'      => $exp->category,
                'amount'        => (float) $exp->amount,
                'description'   => $exp->description,
                'receipt_path'  => $exp->receipt_path,
                'status'        => $exp->status,
                'approved_by'   => $exp->approver?->name,
                'created_at'    => $exp->created_at?->format('d/m/Y'),
            ];
        });

        return response()->json($expenses);
    }

    /**
     * API: Crée une note de frais.
     */
    public function storeExpense(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'employee_id'  => 'required|exists:company_employees,id',
            'category'     => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'description'  => 'required|string',
            'receipt_path' => 'nullable|string|max:255',
        ]);

        // Vérifier que l'employé appartient bien au client
        CompanyEmployee::byClient($clientId)->findOrFail($validated['employee_id']);

        $expense = CompanyExpense::create($validated);

        return response()->json([
            'message' => 'Note de frais créée.',
            'expense' => $expense->fresh(['employee:id,first_name,last_name']),
        ], 201);
    }

    /**
     * API: Approuve ou rejette une note de frais.
     */
    public function approveExpense(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $expense = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $expense->update([
            'status'      => $validated['status'],
            'approved_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Note de frais ' . ($validated['status'] === 'approved' ? 'approuvée' : 'rejetée') . '.',
            'expense' => $expense->fresh(['employee:id,first_name,last_name', 'approver:id,name']),
        ]);
    }

    // ─── STATISTIQUES ─────────────────────────────────────────────────────────

    /**
     * API: Statistiques RH.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $employees = CompanyEmployee::byClient($clientId)->get();
        $totalEmployees = $employees->count();
        $activeEmployees = $employees->where('status', 'active')->count();

        // Congés en cours (approuvés et qui chevauchent la date du jour)
        $ongoingLeaves = CompanyLeaveRequest::whereHas('employee', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })
        ->where('status', 'approved')
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->count();

        // Demandes en attente
        $pendingLeaves = CompanyLeaveRequest::whereHas('employee', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })
        ->where('status', 'pending')
        ->count();

        $pendingExpenses = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })
        ->where('status', 'pending')
        ->count();

        // Total notes de frais du mois
        $monthlyExpenses = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->byClient($clientId);
        })
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->sum('amount');

        // Répartition par type de contrat
        $byContract = $employees->groupBy('contract_type')->map(function ($group) {
            return $group->count();
        });

        // Répartition par département
        $byDepartment = $employees->groupBy('department')->map(function ($group) {
            return $group->count();
        });

        $stats = [
            'total_employees'  => $totalEmployees,
            'active_employees' => $activeEmployees,
            'ongoing_leaves'   => $ongoingLeaves,
            'pending_leaves'   => $pendingLeaves,
            'pending_expenses' => $pendingExpenses,
            'monthly_expenses' => (float) $monthlyExpenses,
            'by_contract'      => $byContract,
            'by_department'    => $byDepartment,
        ];

        return response()->json($stats);
    }
}
