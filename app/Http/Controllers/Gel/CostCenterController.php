<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CostCenterController extends Controller
{
    public function index(Request $request): View
    {
        $query = CostCenter::with('client', 'parent');
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);

        $centers = $query->latest()->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-cost-centers', 'props' => compact('centers', 'clients')]);
    }

    public function show(CostCenter $center): View
    {
        $center->load('client', 'parent', 'children');
        return view('app', ['page' => 'gel-cost-centers-show', 'props' => compact('center')]);
    }

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        $parents = CostCenter::whereNull('parent_id')->get(['id', 'name', 'code']);
        return view('app', ['page' => 'gel-cost-centers-form', 'props' => compact('clients', 'parents')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:department,project,product,region',
            'parent_id' => 'nullable|exists:cost_centers,id',
        ]);

        $center = CostCenter::create($validated);
        AuditTrailService::log($center, 'created', null, $validated, 'Centre de coût créé');

        return redirect()->route('gel.cost-centers.index')->with('success', 'Centre de coût créé.');
    }

    public function update(Request $request, CostCenter $center): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:department,project,product,region',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'is_active' => 'boolean',
        ]);

        $old = $center->getAttributes();
        $center->update($validated);
        AuditTrailService::log($center, 'updated', $old, $center->getAttributes(), 'Centre de coût mis à jour');

        return redirect()->route('gel.cost-centers.index')->with('success', 'Centre de coût mis à jour.');
    }

    public function destroy(CostCenter $center): RedirectResponse
    {
        if ($center->children()->count() > 0) {
            return back()->withErrors(['Supprimez d\'abord les centres de coût enfants.']);
        }
        $old = $center->getAttributes();
        $center->delete();
        AuditTrailService::log($center, 'deleted', $old, null, 'Centre de coût supprimé');
        return redirect()->route('gel.cost-centers.index')->with('success', 'Centre de coût supprimé.');
    }
}
