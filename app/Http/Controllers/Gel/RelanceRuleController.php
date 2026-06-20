<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\RelanceRule;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelanceRuleController extends Controller
{
    public function index(Request $request): View
    {
        $query = RelanceRule::with('client');
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);

        $rules = $query->latest()->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-relance-rules', 'props' => compact('rules', 'clients')]);
    }

    public function show(RelanceRule $rule): View
    {
        $rule->load('client');
        return view('app', ['page' => 'gel-relance-rules-show', 'props' => compact('rule')]);
    }

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-relance-rules-form', 'props' => compact('clients')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'trigger_days' => 'required|integer|min:1',
            'channel' => 'required|in:email,sms,whatsapp,all',
            'template_subject' => 'nullable|string|max:255',
            'template_body' => 'nullable|string',
        ]);

        $rule = RelanceRule::create($validated);
        AuditTrailService::log($rule, 'created', null, $validated, 'Règle de relance créée');

        return redirect()->route('gel.relance-rules.index')->with('success', 'Règle de relance créée.');
    }

    public function update(Request $request, RelanceRule $rule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_days' => 'required|integer|min:1',
            'channel' => 'required|in:email,sms,whatsapp,all',
            'template_subject' => 'nullable|string|max:255',
            'template_body' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $old = $rule->getAttributes();
        $rule->update($validated);
        AuditTrailService::log($rule, 'updated', $old, $rule->getAttributes(), 'Règle de relance mise à jour');

        return redirect()->route('gel.relance-rules.index')->with('success', 'Règle de relance mise à jour.');
    }

    public function destroy(RelanceRule $rule): RedirectResponse
    {
        $old = $rule->getAttributes();
        $rule->delete();
        AuditTrailService::log($rule, 'deleted', $old, null, 'Règle de relance supprimée');
        return redirect()->route('gel.relance-rules.index')->with('success', 'Règle supprimée.');
    }
}
