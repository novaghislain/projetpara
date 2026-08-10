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
    private function getClientId(): int
    {
        $user = Auth::user();
        if (!$user->client_id) {
            abort(403, 'Aucune entreprise associée.');
        }
        return (int) $user->client_id;
    }

    /**
     * Page principale du module RH.
     */
    public function index()
    {
        return view('company', [
            'page' => 'company-hr',
            'clientId' => $this->getClientId(),
        ]);
    }

    // ─── EMPLOYÉS ──────────────────────────────────────────────────

    /**
     * API: Liste des employés.
     */
    public function employees(Request $request)
    {
        $clientId = $this->getClientId();

        $query = CompanyEmployee::where('client_id', $clientId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        $employees = $query->orderBy('last_name')->orderBy('first_name')->paginate(20);

        return response()->json($employees);
    }

    /**
     * API: Détails d'un employé.
     */
    public function employeeShow($id)
    {
        $clientId = $this->getClientId();
        $employee = CompanyEmployee::where('client_id', $clientId)
            ->withCount(['leaveRequests', 'expenses'])
            ->findOrFail($id);

        return response()->json($employee);
    }

    /**
     * API: Créer un employé.
     */
    public function storeEmployee(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'contract_type' => 'required|in:CDI,CDD,INTERIM,STAGE',
            'status' => 'required|in:active,suspended,left',
        ]);

        $validated['client_id'] = $clientId;

        $employee = CompanyEmployee::create($validated);

        return response()->json($employee, 201);
    }

    /**
     * API: Modifier un employé.
     */
    public function updateEmployee(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $employee = CompanyEmployee::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'contract_type' => 'sometimes|in:CDI,CDD,INTERIM,STAGE',
            'status' => 'sometimes|in:active,suspended,left',
        ]);

        $employee->update($validated);

        return response()->json($employee);
    }

    /**
     * API: Supprimer un employé (soft delete).
     */
    public function destroyEmployee($id)
    {
        $clientId = $this->getClientId();
        $employee = CompanyEmployee::where('client_id', $clientId)->findOrFail($id);
        $employee->delete();

        return response()->json(['message' => 'Employé supprimé.']);
    }

    // ─── LISTE DES DÉPARTEMENTS ───────────────────────────────────

    /**
     * API: Liste des départements uniques.
     */
    public function departments()
    {
        $clientId = $this->getClientId();

        $departments = CompanyEmployee::where('client_id', $clientId)
            ->whereNotNull('department')
            ->select('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return response()->json($departments);
    }

    // ─── CONGES ────────────────────────────────────────────────────

    /**
     * API: Liste des demandes de congés.
     */
    public function leaveRequests(Request $request)
    {
        $clientId = $this->getClientId();

        $query = CompanyLeaveRequest::whereHas('employee', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->with(['employee:id,first_name,last_name,department', 'approver:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaves = $query->latest()->paginate(20);

        return response()->json($leaves);
    }

    /**
     * API: Créer une demande de congé.
     */
    public function storeLeaveRequest(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'employee_id' => 'required|exists:company_employees,id',
            'type' => 'required|in:conge,maladie,autre',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:2000',
        ]);

        // Vérifier que l'employé appartient bien au client
        $employee = CompanyEmployee::where('client_id', $clientId)
            ->findOrFail($validated['employee_id']);

        $leave = CompanyLeaveRequest::create([
            'employee_id' => $validated['employee_id'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        $leave->load(['employee:id,first_name,last_name,department']);

        return response()->json($leave, 201);
    }

    /**
     * API: Approuver/Rejeter une demande de congé.
     */
    public function approveLeave(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $leave = CompanyLeaveRequest::whereHas('employee', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leave->update([
            'status' => $validated['status'],
            'approved_by' => Auth::id(),
        ]);

        $leave->load(['employee:id,first_name,last_name,department', 'approver:id,name']);

        return response()->json($leave);
    }

    // ─── NOTES DE FRAIS ────────────────────────────────────────────

    /**
     * API: Liste des notes de frais.
     */
    public function expenses(Request $request)
    {
        $clientId = $this->getClientId();

        $query = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->with(['employee:id,first_name,last_name,department']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $expenses = $query->latest()->paginate(20);

        return response()->json($expenses);
    }

    /**
     * API: Créer une note de frais.
     */
    public function storeExpense(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'employee_id' => 'required|exists:company_employees,id',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'receipt' => 'nullable|file|max:5120', // 5 Mo max
        ]);

        // Vérifier que l'employé appartient au client
        CompanyEmployee::where('client_id', $clientId)->findOrFail($validated['employee_id']);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store("expenses/{$clientId}", 'local');
        }

        $expense = CompanyExpense::create([
            'employee_id' => $validated['employee_id'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'receipt_path' => $receiptPath,
            'status' => 'pending',
        ]);

        $expense->load(['employee:id,first_name,last_name,department']);

        return response()->json($expense, 201);
    }

    /**
     * API: Approuver/Rejeter une note de frais.
     */
    public function approveExpense(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $expense = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $expense->update([
            'status' => $validated['status'],
        ]);

        $expense->load(['employee:id,first_name,last_name,department']);

        return response()->json($expense);
    }

    // ─── STATISTIQUES ──────────────────────────────────────────────

    /**
     * API: Statistiques RH.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $employees = CompanyEmployee::where('client_id', $clientId);

        $stats = [
            'total_employees' => (clone $employees)->count(),
            'active_employees' => (clone $employees)->where('status', 'active')->count(),
            'suspended_employees' => (clone $employees)->where('status', 'suspended')->count(),
            'left_employees' => (clone $employees)->where('status', 'left')->count(),
            'contracts_breakdown' => (clone $employees)
                ->select('contract_type', DB::raw('count(*) as count'))
                ->groupBy('contract_type')
                ->get(),
            'departments_count' => (clone $employees)
                ->whereNotNull('department')
                ->select('department', DB::raw('count(*) as count'))
                ->groupBy('department')
                ->get(),
            'pending_leaves' => CompanyLeaveRequest::whereHas('employee', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })->where('status', 'pending')->count(),
            'pending_expenses' => CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })->where('status', 'pending')->count(),
            'total_salary' => (clone $employees)->sum('salary'),
        ];

        return response()->json($stats);
    }
}
