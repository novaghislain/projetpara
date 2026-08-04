<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Models\Rh\RhPayroll;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des fiches de paie RH.
 *
 * Permet de générer, consulter, changer le statut et supprimer
 * les fiches de paie avec calcul automatique du net à payer.
 */
class RhPayrollsController extends BaseRhController
{
    /**
     * Affiche la liste des fiches de paie ou la vue associée.
     *
     * Si la requête attend du JSON, retourne les fiches de paie paginées
     * avec filtrage optionnel par statut et période.
     *
     * @param Request $request La requête HTTP avec les filtres (statut, periode)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View Liste paginée des fiches de paie ou vue
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
            $query = RhPayroll::whereIn('employee_id', $employeeIds)->with('employee');

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            if ($request->filled('periode')) {
                $query->where('periode', $request->periode);
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-payrolls']);
    }

    /**
     * Génère une nouvelle fiche de paie avec calcul du net à payer.
     *
     * Calcule le net à payer à partir du salaire de base, des primes,
     * indemnités, cotisations, retenues et avances.
     *
     * @param Request $request La requête HTTP contenant les données de paie
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Fiche de paie créée ou redirection
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'periode' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'salaire_base' => 'required|numeric|min:0',
            'primes' => 'nullable|array',
            'indemnites' => 'nullable|array',
            'cotisations' => 'nullable|array',
            'retenues' => 'nullable|array',
            'avance' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['statut'] = 'brouillon';

        // Calcul du net à payer
        $primes = array_sum($validated['primes'] ?? []);
        $indemnites = array_sum($validated['indemnites'] ?? []);
        $cotisations = array_sum($validated['cotisations'] ?? []);
        $retenues = array_sum($validated['retenues'] ?? []);
        $avance = $validated['avance'] ?? 0;

        $validated['net_a_payer'] = $validated['salaire_base'] + $primes + $indemnites - $cotisations - $retenues - $avance;

        $payroll = RhPayroll::create($validated);

        if ($request->expectsJson()) {
            return response()->json($payroll->load('employee'), 201);
        }
        return redirect()->route('rh.payrolls.index')->with('success', 'Fiche de paie générée.');
    }

    /**
     * Affiche les détails d'une fiche de paie.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant de la fiche de paie
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View Détails de la fiche de paie ou vue
     */
    public function show(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $payroll = RhPayroll::whereIn('employee_id', $employeeIds)->with('employee')->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json($payroll);
        }
        return view('app', ['page' => 'rh-payrolls-show', 'id' => $id]);
    }

    /**
     * Modifie le statut d'une fiche de paie (calculée, validée, payée, annulée).
     *
     * Met à jour les informations associées au changement de statut
     * comme l'identifiant du valideur et la date de paiement.
     *
     * @param Request $request La requête HTTP contenant le nouveau statut
     * @param mixed $id L'identifiant de la fiche de paie
     * @return \Illuminate\Http\JsonResponse La fiche de paie mise à jour
     */
    public function changerStatut(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $payroll = RhPayroll::whereIn('employee_id', $employeeIds)->findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|string|in:calcule,valide,paye,annule',
        ]);

        $data = ['statut' => $validated['statut']];
        if ($validated['statut'] === 'valide') {
            $data['valide_par'] = Auth::id();
        }
        if ($validated['statut'] === 'paye') {
            $data['date_paiement'] = now();
        }
        $payroll->update($data);

        return response()->json($payroll->load('employee'));
    }

    /**
     * Supprime une fiche de paie.
     *
     * @param Request $request La requête HTTP
     * @param mixed $id L'identifiant de la fiche de paie à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Message de confirmation ou redirection
     */
    public function destroy(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $payroll = RhPayroll::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $payroll->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Fiche de paie supprimée.']);
        }
        return redirect()->route('rh.payrolls.index')->with('success', 'Fiche de paie supprimée.');
    }
}
