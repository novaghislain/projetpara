<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Http\Controllers\Controller;
use App\Models\Rh\RhContract;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RhContractsController extends Controller
{
    protected function getClientId(Request $request)
    {
        return $request->input('client_id') ?: Auth::user()?->client_id;
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
            $query = RhContract::whereIn('employee_id', $employeeIds)->with('employee');

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('reference', 'like', "%{$s}%")
                      ->orWhere('poste', 'like', "%{$s}%");
                });
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-contracts']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'reference' => 'nullable|string|max:100',
            'type' => 'required|string|in:CDI,CDD,INTERIM,STAGE,PRESTATION',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'duree_mois' => 'nullable|integer|min:1',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'salaire' => 'nullable|numeric|min:0',
            'periode_essai_jours' => 'nullable|integer|min:0',
            'renouvelable' => 'nullable|boolean',
            'date_fin_periode_essai' => 'nullable|date',
            'statut' => 'nullable|string|in:brouillon,actif,expire,resilie,renouvele',
            'fichier_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['created_by'] = Auth::id();
        $contract = RhContract::create($validated);

        if ($request->expectsJson()) {
            return response()->json($contract->load('employee'), 201);
        }
        return redirect()->route('rh.contracts.index')->with('success', 'Contrat créé.');
    }

    public function update(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $contract = RhContract::whereIn('employee_id', $employeeIds)->findOrFail($id);

        $validated = $request->validate([
            'type' => 'sometimes|required|string|in:CDI,CDD,INTERIM,STAGE,PRESTATION',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'duree_mois' => 'nullable|integer|min:1',
            'poste' => 'nullable|string|max:255',
            'salaire' => 'nullable|numeric|min:0',
            'renouvelable' => 'nullable|boolean',
            'statut' => 'nullable|string|in:brouillon,actif,expire,resilie,renouvele',
            'notes' => 'nullable|string|max:2000',
        ]);

        $contract->update($validated);

        if ($request->expectsJson()) {
            return response()->json($contract->load('employee'));
        }
        return redirect()->route('rh.contracts.index')->with('success', 'Contrat mis à jour.');
    }

    public function destroy(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $contract = RhContract::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $contract->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Contrat supprimé.']);
        }
        return redirect()->route('rh.contracts.index')->with('success', 'Contrat supprimé.');
    }

    public function changerStatut(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $contract = RhContract::whereIn('employee_id', $employeeIds)->findOrFail($id);

        $validated = $request->validate(['statut' => 'required|string|in:brouillon,actif,expire,resilie,renouvele']);
        $contract->update($validated);

        return response()->json($contract);
    }
}
