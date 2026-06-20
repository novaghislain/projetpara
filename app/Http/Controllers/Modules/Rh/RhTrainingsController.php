<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Http\Controllers\Controller;
use App\Models\Rh\RhTraining;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;

class RhTrainingsController extends Controller
{
    protected function getClientId(Request $request)
    {
        return $request->input('client_id') ?: Auth::user()?->client_id;
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
            $query = RhTraining::whereIn('employee_id', $employeeIds)->with('employee');

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-trainings']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'titre' => 'required|string|max:255',
            'organisme' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'duree_heures' => 'nullable|numeric|min:0',
            'cout' => 'nullable|numeric|min:0',
            'type' => 'nullable|string|in:interne,externe,en_ligne',
            'certificat_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['statut'] = 'planifie';
        $training = RhTraining::create($validated);

        if ($request->expectsJson()) {
            return response()->json($training->load('employee'), 201);
        }
        return redirect()->route('rh.trainings.index')->with('success', 'Formation créée.');
    }

    public function update(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $training = RhTraining::whereIn('employee_id', $employeeIds)->findOrFail($id);

        $validated = $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'organisme' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'duree_heures' => 'nullable|numeric|min:0',
            'cout' => 'nullable|numeric|min:0',
            'type' => 'nullable|string|in:interne,externe,en_ligne',
            'statut' => 'nullable|string|in:planifie,en_cours,termine,annule',
            'certificat_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $training->update($validated);

        if ($request->expectsJson()) {
            return response()->json($training->load('employee'));
        }
        return redirect()->route('rh.trainings.index')->with('success', 'Formation mise à jour.');
    }

    public function destroy(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $training = RhTraining::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $training->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Formation supprimée.']);
        }
        return redirect()->route('rh.trainings.index')->with('success', 'Formation supprimée.');
    }
}
