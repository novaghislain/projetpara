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

/**
 * Contrôleur RH (Ressources Humaines) pour l'interface Company.
 *
 * Gère les employés, les congés, les dépenses, les fiches de paie
 * et les formations. Point d'entrée unique pour le module RH.
 *
 * Toutes les données sont filtrées par client_id via le scope byClient.
 */
class CompanyRhController extends BaseCompanyController
{
    /**
     * Page du tableau de bord RH (vue SPA).
     * Retourne les statistiques si la requête attend du JSON.
     *
     * @param Request $request Requête HTTP
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->stats($request);
        }
        return view('company', ['page' => 'company-rh-dashboard', 'clientId' => $this->getClientId()]);
    }

    /**
     * API: Statistiques RH (effectifs, congés en attente, dépenses).
     *
     * @param Request $request Requête HTTP
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Page vue SPA de la liste des employés.
     *
     * @param Request $request Requête HTTP
     * @return \Illuminate\View\View
     */
    public function employees(Request $request)
    {
        return view('company', ['page' => 'company-rh-employees', 'clientId' => $this->getClientId()]);
    }

    /**
     * API: Liste des employés avec recherche et pagination.
     *
     * @param Request $request Requête HTTP (search optionnel)
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * API: Affiche un employé avec ses relations (contrats, congés, paies, formations).
     *
     * @param int $id Identifiant de l'employé
     * @return \Illuminate\Http\JsonResponse
     */
    public function employeeShow($id)
    {
        $employee = RhEmployee::byClient($this->getClientId())
            ->with(['contracts', 'leaveRequests', 'payrolls', 'trainings'])
            ->findOrFail($id);
        return response()->json($employee);
    }

    /**
     * API: Crée un nouvel employé.
     *
     * @param Request $request Requête HTTP (nom, prenom, email, phone, poste, etc.)
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * API: Modifie un employé.
     *
     * @param Request $request Requête HTTP avec les champs à modifier
     * @param int $id Identifiant de l'employé
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * API: Supprime un employé.
     *
     * @param int $id Identifiant de l'employé
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyEmployee($id)
    {
        $employee = RhEmployee::byClient($this->getClientId())->findOrFail($id);
        $employee->delete();
        return response()->json(['message' => 'Employé supprimé.']);
    }

    // -- Leaves --

    /**
     * Page vue SPA de la gestion des congés.
     *
     * @param Request $request Requête HTTP
     * @return \Illuminate\View\View
     */
    public function leaves(Request $request)
    {
        return view('company', ['page' => 'company-rh-leaves', 'clientId' => $this->getClientId()]);
    }

    /**
     * API: Liste des demandes de congés (avec filtre par statut).
     *
     * @param Request $request Requête HTTP (statut optionnel)
     * @return \Illuminate\Http\JsonResponse
     */
    public function leavesList(Request $request)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId())->pluck('id');
        $query = RhLeaveRequest::whereIn('employee_id', $employeeIds)->with('employee');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        return response()->json($query->latest()->paginate(20));
    }

    /**
     * API: Crée une demande de congé.
     *
     * @param Request $request Requête HTTP (employee_id, type, date_debut, date_fin, motif)
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * API: Approuve ou rejette une demande de congé.
     *
     * @param Request $request Requête HTTP (statut, notes_approbateur)
     * @param int $id Identifiant de la demande de congé
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Page vue SPA de la gestion des dépenses.
     *
     * @param Request $request Requête HTTP
     * @return \Illuminate\View\View
     */
    public function expenses(Request $request)
    {
        return view('company', ['page' => 'company-rh-expenses', 'clientId' => $this->getClientId()]);
    }

    /**
     * API: Liste des notes de frais (avec filtre par statut).
     *
     * @param Request $request Requête HTTP (statut optionnel)
     * @return \Illuminate\Http\JsonResponse
     */
    public function expensesList(Request $request)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId())->pluck('id');
        $query = RhExpense::whereIn('employee_id', $employeeIds)->with('employee');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        return response()->json($query->latest()->paginate(20));
    }

    /**
     * API: Crée une note de frais.
     *
     * @param Request $request Requête HTTP (employee_id, categorie, montant, description)
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * API: Approuve ou rejette une note de frais.
     *
     * @param Request $request Requête HTTP (statut)
     * @param int $id Identifiant de la note de frais
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Page vue SPA de la gestion des fiches de paie.
     *
     * @param Request $request Requête HTTP
     * @return \Illuminate\View\View
     */
    public function payrolls(Request $request)
    {
        return view('company', ['page' => 'company-rh-payrolls', 'clientId' => $this->getClientId()]);
    }

    /**
     * API: Liste des fiches de paie (avec filtre par statut).
     *
     * @param Request $request Requête HTTP (statut optionnel)
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Page vue SPA de la gestion des formations.
     *
     * @param Request $request Requête HTTP
     * @return \Illuminate\View\View
     */
    public function trainings(Request $request)
    {
        return view('company', ['page' => 'company-rh-trainings', 'clientId' => $this->getClientId()]);
    }

    /**
     * API: Liste des formations (avec filtre par statut).
     *
     * @param Request $request Requête HTTP (statut optionnel)
     * @return \Illuminate\Http\JsonResponse
     */
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
