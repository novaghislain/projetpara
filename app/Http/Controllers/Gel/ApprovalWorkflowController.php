<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalWorkflowController extends Controller
{
    public function index(Request $request): View
    {
        $query = ApprovalWorkflow::with('client');
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);

        $workflows = $query->latest()->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-approval-workflows', 'props' => compact('workflows', 'clients')]);
    }

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-approval-workflows-form', 'props' => compact('clients')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'trigger_model' => 'required|string|max:100',
            'trigger_condition' => 'nullable|array',
            'steps' => 'required|array|min:1',
        ]);

        $workflow = ApprovalWorkflow::create($validated);
        AuditTrailService::log($workflow, 'created', null, $validated, 'Workflow d\'approbation créé');

        return redirect()->route('gel.approval-workflows.index')->with('success', 'Workflow créé.');
    }

    public function show(ApprovalWorkflow $workflow): View
    {
        $workflow->load('client');
        return view('app', ['page' => 'gel-approval-workflows-show', 'props' => compact('workflow')]);
    }

    public function update(Request $request, ApprovalWorkflow $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_model' => 'required|string|max:100',
            'trigger_condition' => 'nullable|array',
            'steps' => 'required|array|min:1',
            'is_active' => 'boolean',
        ]);

        $old = $workflow->getAttributes();
        $workflow->update($validated);
        AuditTrailService::log($workflow, 'updated', $old, $workflow->getAttributes(), 'Workflow mis à jour');

        return redirect()->route('gel.approval-workflows.show', $workflow)->with('success', 'Workflow mis à jour.');
    }

    public function destroy(ApprovalWorkflow $workflow): RedirectResponse
    {
        $old = $workflow->getAttributes();
        $workflow->delete();
        AuditTrailService::log($workflow, 'deleted', $old, null, 'Workflow supprimé');
        return redirect()->route('gel.approval-workflows.index')->with('success', 'Workflow supprimé.');
    }
}
