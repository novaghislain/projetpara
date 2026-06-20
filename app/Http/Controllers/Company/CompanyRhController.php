<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Rh\RhEmployee;
use App\Models\Rh\RhLeaveRequest;
use App\Models\Rh\RhExpense;
use App\Models\Rh\RhPayroll;
use App\Models\Rh\RhTraining;
use App\Models\Rh\RhAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyRhController extends Controller
{
    protected function getClientId()
    {
        return Auth::user()?->client_id;
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->stats($request);
        }
        return view('company', ['page' => 'company-rh-dashboard', 'clientId' => $this->getClientId()]);
    }

    public function stats(Request $request)
    {
        $clientId = $this->getClientId();
        $employees = RhEmployee::byClient($clientId);
        $employeeIds = $employees->pluck('id');

        return response()->json([
            'total_employees' => $employees->count(),
            'active_employees' => $employees->where('status', 'actif')->count(),
            'pending_leaves' => RhLeaveRequest::whereIn('employee_id', $employeeIds)->where('statut', 'pending')->count(),
            'pending_expenses' => RhExpense::whereIn('employee_id', $employeeIds)->where('statut', 'pending')->count(),
            'recent_employees' => $employees->latest()->take(5)->get()->map(fn($e) => [
                'id' => $e->id,
                'nom' => $e->nom,
                'prenom' => $e->prenom,
                'poste' => $e->poste,
                'status' => $e->status,
                'photo' => $e->photo,
            ]),
        ]);
    }

    // -- Employees --

    public function employees(Request $request)
    {
        return view('company', ['page' => 'company-rh-employees', 'clientId' => $this->getClientId()]);
    }

    public function employeesList(Request $request)
    {
        $query = RhEmployee::byClient($this->getClientId())->with('contracts');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%{$s}%")
                  ->orWhere('prenom', 'like', "%{$s}%")
                  ->orWhere('matricule', 'like', "%{$s}%")
                  ->orWhere('poste', 'like', "%{$s}%");
            });
        }
        return response()->json($query->latest()->paginate(20));
    }

    public function employeeShow($id)
    {
        $employee = RhEmployee::byClient($this->getClientId())
            ->with(['contracts', 'leaveRequests', 'payrolls', 'trainings'])
            ->findOrFail($id);
        return response()->json($employee);
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'date_embauche' => 'nullable|date',
            'type_contrat' => 'nullable|string|in:CDI,CDD,INTERIM,STAGE,PRESTATION',
            'salaire_base' => 'nullable|numeric|min:0',
        ]);
        $validated['client_id'] = $this->getClientId();
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'actif';
        $employee = RhEmployee::create($validated);
        return response()->json($employee, 201);
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId())->findOrFail($id);
        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'sometimes|required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'salaire_base' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:actif,suspendu,quitte',
        ]);
        $employee->update($validated);
        return response()->json($employee);
    }

    public function destroyEmployee($id)
    {
        $employee = RhEmployee::byClient($this->getClientId())->findOrFail($id);
        $employee->delete();
        return response()->json(['message' => 'Employé supprimé.']);
    }

    // -- Leaves --

    public function leaves(Request $request)
    {
        return view('company', ['page' => 'company-rh-leaves', 'clientId' => $this->getClientId()]);
    }

    public function leavesList(Request $request)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId())->pluck('id');
        $query = RhLeaveRequest::whereIn('employee_id', $employeeIds)->with('employee');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        return response()->json($query->latest()->paginate(20));
    }

    public function leaveStore(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'type' => 'required|string|in:conge,maladie,maternite,paternite,formation,autre',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string|max:2000',
        ]);
        $validated['statut'] = 'pending';
        $leave = RhLeaveRequest::create($validated);
        return response()->json($leave->load('employee'), 201);
    }

    public function leaveApprouver(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId())->pluck('id');
        $leave = RhLeaveRequest::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $validated = $request->validate([
            'statut' => 'required|string|in:approved,rejected,cancelled',
            'notes_approbateur' => 'nullable|string|max:2000',
        ]);
        $validated['approbateur_id'] = Auth::id();
        $validated['date_approbation'] = now();
        $leave->update($validated);
        return response()->json($leave->load('employee'));
    }

    // -- Expenses --

    public function expenses(Request $request)
    {
        return view('company', ['page' => 'company-rh-expenses', 'clientId' => $this->getClientId()]);
    }

    public function expensesList(Request $request)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId())->pluck('id');
        $query = RhExpense::whereIn('employee_id', $employeeIds)->with('employee');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        return response()->json($query->latest()->paginate(20));
    }

    public function expenseStore(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'categorie' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
        ]);
        $validated['statut'] = 'pending';
        $expense = RhExpense::create($validated);
        return response()->json($expense->load('employee'), 201);
    }

    public function expenseApprouver(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId())->pluck('id');
        $expense = RhExpense::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $validated = $request->validate([
            'statut' => 'required|string|in:approved,rejected,paid',
        ]);
        $validated['approbateur_id'] = Auth::id();
        $validated['date_approbation'] = now();
        $expense->update($validated);
        return response()->json($expense->load('employee'));
    }

    // -- Payrolls --

    public function payrolls(Request $request)
    {
        return view('company', ['page' => 'company-rh-payrolls', 'clientId' => $this->getClientId()]);
    }

    public function payrollsList(Request $request)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId())->pluck('id');
        $query = RhPayroll::whereIn('employee_id', $employeeIds)->with('employee');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        return response()->json($query->latest()->paginate(20));
    }

    // -- Trainings --

    public function trainings(Request $request)
    {
        return view('company', ['page' => 'company-rh-trainings', 'clientId' => $this->getClientId()]);
    }

    public function trainingsList(Request $request)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId())->pluck('id');
        $query = RhTraining::whereIn('employee_id', $employeeIds)->with('employee');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        return response()->json($query->latest()->paginate(20));
    }
}
