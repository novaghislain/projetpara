<?php

namespace App\Http\Controllers\GelSecretary\Services;

use App\Http\Controllers\Controller;
use App\Models\Rh\RhEmployee;
use App\Models\Rh\RhLeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrController extends Controller
{
    public function index(Request $request)
    {
        $activeClientId = session('active_client_id');
        $activeClient = $activeClientId ? \App\Models\Gel\Client::find($activeClientId) : null;

        if (!$activeClient) {
            $employees = collect();
            $leaves = collect();
        } else {
            $employees = RhEmployee::where('client_id', $activeClient->id)->orderBy('nom')->get();
            $leaves = RhLeaveRequest::whereHas('employee', function ($q) use ($activeClient) {
                $q->where('client_id', $activeClient->id);
            })->with('employee')->orderBy('created_at', 'desc')->get();
        }

        return view('gel-secretary.services.hr', compact('employees', 'leaves', 'activeClient'));
    }

    public function storeEmployee(Request $request)
    {
        $activeClientId = session('active_client_id');
        $activeClient = $activeClientId ? \App\Models\Gel\Client::find($activeClientId) : null;
        if (!$activeClient) {
            return back()->with('error', 'Veuillez sélectionner un client.');
        }

        $validated = $request->validate([
            'matricule' => 'nullable|string|max:50',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'poste' => 'nullable|string|max:100',
            'date_embauche' => 'nullable|date',
            'type_contrat' => 'nullable|string|max:50'
        ]);

        $validated['client_id'] = $activeClient->id;
        $validated['created_by'] = Auth::id();

        RhEmployee::create($validated);

        return back()->with('success', 'Employé ajouté avec succès.');
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = RhEmployee::findOrFail($id);
        
        $validated = $request->validate([
            'matricule' => 'nullable|string|max:50',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'poste' => 'nullable|string|max:100',
            'date_embauche' => 'nullable|date',
            'type_contrat' => 'nullable|string|max:50'
        ]);

        $employee->update($validated);

        return back()->with('success', 'Employé mis à jour avec succès.');
    }

    public function storeLeave(Request $request)
    {
        $activeClientId = session('active_client_id');
        $activeClient = $activeClientId ? \App\Models\Gel\Client::find($activeClientId) : null;
        if (!$activeClient) {
            return back()->with('error', 'Veuillez sélectionner un client.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'type' => 'required|in:conge,maladie,maternite,paternite,formation,autre',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string'
        ]);

        $start = \Carbon\Carbon::parse($validated['date_debut']);
        $end = \Carbon\Carbon::parse($validated['date_fin']);
        $validated['duree_jours'] = $start->diffInDays($end) + 1;
        $validated['statut'] = 'pending';

        RhLeaveRequest::create($validated);

        return back()->with('success', 'Demande de congé enregistrée avec succès.');
    }

    public function updateLeaveStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|in:approved,rejected,cancelled',
            'notes_approbateur' => 'nullable|string'
        ]);

        $leave = RhLeaveRequest::findOrFail($id);
        
        $leave->update([
            'statut' => $validated['statut'],
            'approbateur_id' => Auth::id(),
            'notes_approbateur' => $validated['notes_approbateur'] ?? null,
            'date_approbation' => now(),
        ]);

        return back()->with('success', 'Statut du congé mis à jour.');
    }
}
