<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ItAsset;
use App\Models\Client;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItAssetController extends Controller
{
    public function index(Request $request): View
    {
        $query = ItAsset::with(['client', 'assignedTo']);

        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('status')) $query->where('status', $request->status);

        $assets = $query->latest()->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);

        return view('app', ['page' => 'gel-it-assets', 'props' => compact('assets', 'clients')]);
    }

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        $technicians = User::where('role', 'super_admin')->orWhere('is_admin', true)->get(['id', 'name']);
        return view('app', ['page' => 'gel-it-assets-form', 'props' => compact('clients', 'technicians')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'asset_tag' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'category' => 'required|in:computer,server,printer,network,mobile,software,other',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,in_repair,disposed',
            'assigned_to_user' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expires_at' => 'nullable|date',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'notes' => 'nullable|string',
        ]);

        $asset = ItAsset::create($validated);
        AuditTrailService::log($asset, 'created', null, $validated, 'Équipement IT créé');

        return redirect()->route('gel.it-assets.index')->with('success', 'Équipement créé avec succès.');
    }

    public function show(ItAsset $asset): View
    {
        $asset->load(['client', 'assignedTo', 'licenses', 'interventions.technician']);
        return view('app', ['page' => 'gel-it-assets-show', 'props' => compact('asset')]);
    }

    public function update(Request $request, ItAsset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'category' => 'required|in:computer,server,printer,network,mobile,software,other',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,in_repair,disposed',
            'assigned_to_user' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expires_at' => 'nullable|date',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'notes' => 'nullable|string',
        ]);

        $old = $asset->getAttributes();
        $asset->update($validated);
        AuditTrailService::log($asset, 'updated', $old, $asset->getAttributes(), 'Équipement IT mis à jour');

        return redirect()->route('gel.it-assets.show', $asset)->with('success', 'Équipement mis à jour.');
    }

    public function destroy(ItAsset $asset): RedirectResponse
    {
        $old = $asset->getAttributes();
        $asset->delete();
        AuditTrailService::log($asset, 'deleted', $old, null, 'Équipement IT supprimé');

        return redirect()->route('gel.it-assets.index')->with('success', 'Équipement supprimé.');
    }
}
