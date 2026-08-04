<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Models\Rh\RhAttendance;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des pointages / présences RH.
 *
 * Permet de consulter, enregistrer et supprimer les pointages
 * des employés avec filtrage par date et type de présence.
 */
class RhAttendanceController extends BaseRhController
{
    /**
     * Affiche la liste des pointages ou la vue associée.
     *
     * Si la requête attend du JSON, retourne les pointages paginés
     * avec filtrage optionnel par date et type de présence.
     *
     * @param Request $request La requête HTTP avec les filtres (date_from, date_to, type_presence)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View Liste paginée des pointages ou vue
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
            $query = RhAttendance::whereIn('employee_id', $employeeIds)->with('employee');

            if ($request->filled('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }
            if ($request->filled('type_presence')) {
                $query->where('type_presence', $request->type_presence);
            }
            return response()->json($query->latest('date')->paginate(20));
        }
        return view('app', ['page' => 'rh-attendance']);
    }

    /**
     * Enregistre ou met à jour un pointage.
     *
     * @param Request $request La requête HTTP contenant les données du pointage
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Pointage créé ou redirection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'date' => 'required|date',
            'heure_arrivee' => 'nullable|date_format:H:i',
            'heure_depart' => 'nullable|date_format:H:i',
            'pauses' => 'nullable|array',
            'heures_travaillees' => 'nullable|numeric|min:0|max:24',
            'type_presence' => 'nullable|string|in:present,abs,retard,conge,mission',
            'justificatif' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $attendance = RhAttendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        if ($request->expectsJson()) {
            return response()->json($attendance->load('employee'), 201);
        }
        return redirect()->route('rh.attendance.index')->with('success', 'Pointage enregistré.');
    }

    /**
     * Supprime un pointage.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant du pointage à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Message de confirmation ou redirection
     */
    public function destroy(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $attendance = RhAttendance::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $attendance->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pointage supprimé.']);
        }
        return redirect()->route('rh.attendance.index')->with('success', 'Pointage supprimé.');
    }
}
