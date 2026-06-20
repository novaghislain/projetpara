<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Http\Controllers\Controller;
use App\Models\Rh\RhPayroll;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RhPayrollsController extends Controller
{
    protected function getClientId(Request $request)
    {
        return $request->input('client_id') ?: Auth::user()?->client_id;
    }

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

    public function show(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $payroll = RhPayroll::whereIn('employee_id', $employeeIds)->with('employee')->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json($payroll);
        }
        return view('app', ['page' => 'rh-payrolls-show', 'id' => $id]);
    }

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
