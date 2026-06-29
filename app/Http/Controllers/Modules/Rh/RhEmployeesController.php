<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RhEmployeesController extends BaseRhController
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = RhEmployee::byClient($this->getClientId($request))->with(['contracts']);
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('nom', 'like', "%{$s}%")
                      ->orWhere('prenom', 'like', "%{$s}%")
                      ->orWhere('matricule', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                      ->orWhere('poste', 'like', "%{$s}%");
                });
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-employees']);
    }

    public function create(Request $request)
    {
        return view('app', ['page' => 'rh-employees-create']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule' => 'nullable|string|max:50',
            'civilite' => 'nullable|string|in:M.,Mme,Mlle',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'situation_matrimoniale' => 'nullable|string|in:celibataire,marié(e),divorcé(e),veuf(ve)',
            'nombre_enfants' => 'nullable|integer|min:0',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'date_embauche' => 'nullable|date',
            'date_depart' => 'nullable|date',
            'type_contrat' => 'nullable|string|in:CDI,CDD,INTERIM,STAGE,PRESTATION',
            'salaire_base' => 'nullable|numeric|min:0',
            'cnss_number' => 'nullable|string|max:50',
            'ifu_number' => 'nullable|string|max:50',
            'banque' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:50',
            'urgence_nom' => 'nullable|string|max:255',
            'urgence_phone' => 'nullable|string|max:50',
            'photo' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:actif,suspendu,quitte',
        ]);

        $validated['client_id'] = $this->getClientId($request);
        $validated['created_by'] = Auth::id();

        $employee = RhEmployee::create($validated);

        if ($request->expectsJson()) {
            return response()->json($employee->load('contracts'), 201);
        }
        return redirect()->route('rh.employees.index')->with('success', 'Employé créé avec succès.');
    }

    public function show(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId($request))
            ->with(['contracts', 'leaveRequests', 'expenses', 'payrolls', 'attendance', 'trainings'])
            ->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json($employee);
        }
        return view('app', ['page' => 'rh-employees-show', 'id' => $id]);
    }

    public function edit(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId($request))->findOrFail($id);
        return view('app', ['page' => 'rh-employees-edit', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId($request))->findOrFail($id);

        $validated = $request->validate([
            'matricule' => 'nullable|string|max:50',
            'civilite' => 'nullable|string|in:M.,Mme,Mlle',
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'sometimes|required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'situation_matrimoniale' => 'nullable|string|in:celibataire,marié(e),divorcé(e),veuf(ve)',
            'nombre_enfants' => 'nullable|integer|min:0',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'date_embauche' => 'nullable|date',
            'date_depart' => 'nullable|date',
            'type_contrat' => 'nullable|string|in:CDI,CDD,INTERIM,STAGE,PRESTATION',
            'salaire_base' => 'nullable|numeric|min:0',
            'cnss_number' => 'nullable|string|max:50',
            'ifu_number' => 'nullable|string|max:50',
            'banque' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:50',
            'urgence_nom' => 'nullable|string|max:255',
            'urgence_phone' => 'nullable|string|max:50',
            'photo' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:actif,suspendu,quitte',
        ]);

        $employee->update($validated);

        if ($request->expectsJson()) {
            return response()->json($employee->load('contracts'));
        }
        return redirect()->route('rh.employees.show', $id)->with('success', 'Employé mis à jour.');
    }

    public function destroy(Request $request, $id)
    {
        $employee = RhEmployee::byClient($this->getClientId($request))->findOrFail($id);
        $employee->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Employé supprimé.']);
        }
        return redirect()->route('rh.employees.index')->with('success', 'Employé supprimé.');
    }
}
