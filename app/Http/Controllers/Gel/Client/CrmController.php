<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Crm\Lead;
use App\Models\Crm\Opportunity;
use App\Services\Crm\CrmService;

class CrmController extends Controller
{
    protected CrmService $crmService;

    public function __construct(CrmService $crmService)
    {
        $this->crmService = $crmService;
    }

    public function createLead(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'contact_name' => 'required|string',
            'company_name' => 'nullable|string',
            'email' => 'nullable|email'
        ]);

        $lead = Lead::create($request->all());

        return response()->json([
            'status' => 'success',
            'lead' => $lead,
            'message' => 'Prospect créé avec succès.'
        ]);
    }

    public function convertToOpportunity(Request $request, $leadId)
    {
        $lead = Lead::findOrFail($leadId);
        
        $request->validate([
            'title' => 'required|string',
            'expected_revenue' => 'required|numeric'
        ]);

        $opportunity = Opportunity::create([
            'lead_id' => $lead->id,
            'title' => $request->title,
            'expected_revenue' => $request->expected_revenue,
            'stage' => 'new',
            'probability' => 10
        ]);

        $lead->update(['status' => 'qualified']);

        return response()->json([
            'status' => 'success',
            'opportunity' => $opportunity,
            'message' => 'Prospect converti en opportunité.'
        ]);
    }

    public function advanceOpportunity($id)
    {
        $opportunity = $this->crmService->advanceOpportunityStage($id);

        return response()->json([
            'status' => 'success',
            'opportunity' => $opportunity,
            'message' => 'Opportunité avancée au stade : ' . $opportunity->stage
        ]);
    }
}
