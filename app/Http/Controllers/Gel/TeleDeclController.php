<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AccountingTaxDeclaration;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeleDeclController extends Controller
{
    public function index(Request $request): View
    {
        $query = AccountingTaxDeclaration::with('client')
            ->whereIn('tax_type', ['tva', 'is', 'its', 'cnss', 'vps', 'aib']);

        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('tax_type')) $query->where('tax_type', $request->tax_type);
        if ($request->filled('status')) $query->where('status', $request->status);

        $declarations = $query->latest()->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-tele-declarations', 'props' => compact('declarations', 'clients')]);
    }

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-tele-declarations-form', 'props' => compact('clients')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'tax_type'      => 'required|in:tva,is,its,cnss,vps,aib',
            'period_month'  => 'nullable|integer|between:1,12',
            'period_quarter'=> 'nullable|integer|between:1,4',
            'period_year'   => 'required|integer|min:2020|max:2050',
            'date_debut'    => 'required|date',
            'date_fin'      => 'required|date|after_or_equal:date_debut',
            'date_echeance' => 'required|date',
            'montant_dut'   => 'required|numeric|min:0',
            'base_imposable'=> 'nullable|numeric|min:0',
            'taux'          => 'nullable|numeric|min:0|max:100',
            'notes'         => 'nullable|string',
        ]);

        $validated['period_type'] = $validated['period_quarter'] ? 'trimestriel'
            : ($validated['period_month'] ? 'mensuel' : 'annuel');
        $validated['status'] = 'brouillon';
        $validated['created_by'] = auth()->id();

        $declaration = AccountingTaxDeclaration::create($validated);

        AuditTrailService::log($declaration, 'created', null, $validated, 'Déclaration fiscale créée');

        return redirect()->route('gel.tele-declarations.index')->with('success', 'Déclaration fiscale créée.');
    }

    public function show(AccountingTaxDeclaration $declaration): View
    {
        $declaration->load('client', 'createdBy');
        return view('app', ['page' => 'gel-tele-declarations-show', 'props' => compact('declaration')]);
    }

    public function submit(AccountingTaxDeclaration $declaration): RedirectResponse
    {
        if ($declaration->status !== 'brouillon') {
            return back()->withErrors(['Seules les déclarations en brouillon peuvent être soumises.']);
        }

        $old = $declaration->getAttributes();
        $declaration->update([
            'status'     => 'depose',
            'date_depot' => now(),
        ]);
        AuditTrailService::log($declaration, 'submitted', $old, $declaration->getAttributes(), 'Déclaration déposée');

        return redirect()->route('gel.tele-declarations.show', $declaration)
            ->with('success', 'Déclaration déposée avec succès.');
    }

    public function destroy(AccountingTaxDeclaration $declaration): RedirectResponse
    {
        if (!in_array($declaration->status, ['brouillon'])) {
            return back()->withErrors(['Seules les déclarations en brouillon peuvent être supprimées.']);
        }
        $old = $declaration->getAttributes();
        $declaration->delete();
        AuditTrailService::log($declaration, 'deleted', $old, null, 'Déclaration supprimée');
        return redirect()->route('gel.tele-declarations.index')->with('success', 'Déclaration supprimée.');
    }
}
