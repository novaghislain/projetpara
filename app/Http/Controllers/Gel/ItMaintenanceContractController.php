<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ItMaintenanceContract;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItMaintenanceContractController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $query = ItMaintenanceContract::with('client');
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('status')) $query->where('status', $request->status);

        $contracts = $query->latest()->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($contracts);
        }

        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-it-maintenance-contracts', 'props' => compact('contracts', 'clients')]);
    }

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-it-maintenance-contracts-form', 'props' => compact('clients')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'reference' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'type' => 'required|in:corrective,preventive,full_service,hotline',
            'status' => 'required|in:active,expired,suspended',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'monthly_amount' => 'nullable|numeric',
            'included_hours' => 'required|integer|min:0',
            'response_time_hours' => 'required|integer|min:1',
            'coverage_hours' => 'required|string|max:50',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $contract = ItMaintenanceContract::create($validated);
        AuditTrailService::log($contract, 'created', null, $validated, 'Contrat maintenance créé');

        return redirect()->route('gel.it-maintenance-contracts.index')->with('success', 'Contrat créé.');
    }

    public function show(ItMaintenanceContract $contract): View
    {
        $contract->load('client');
        return view('app', ['page' => 'gel-it-maintenance-contracts-show', 'props' => compact('contract')]);
    }

    public function update(Request $request, ItMaintenanceContract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'type' => 'required|in:corrective,preventive,full_service,hotline',
            'status' => 'required|in:active,expired,suspended',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'monthly_amount' => 'nullable|numeric',
            'included_hours' => 'required|integer|min:0',
            'response_time_hours' => 'required|integer|min:1',
            'coverage_hours' => 'required|string|max:50',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $old = $contract->getAttributes();
        $contract->update($validated);
        AuditTrailService::log($contract, 'updated', $old, $contract->getAttributes(), 'Contrat maintenance mis à jour');

        return redirect()->route('gel.it-maintenance-contracts.index')->with('success', 'Contrat mis à jour.');
    }

    public function destroy(ItMaintenanceContract $contract): RedirectResponse
    {
        $old = $contract->getAttributes();
        $contract->delete();
        AuditTrailService::log($contract, 'deleted', $old, null, 'Contrat maintenance supprimé');
        return redirect()->route('gel.it-maintenance-contracts.index')->with('success', 'Contrat supprimé.');
    }
}
