<?php

namespace App\Http\Controllers\GelCommunication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\MarketingBrief;
use App\Models\Gel\MarketingCampaign;

class BriefController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        
        $briefs = MarketingBrief::with('client')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        $briefs->appends(['status' => $status]);

        return view('gel-communication.briefs.index', compact('briefs', 'status'));
    }

    public function show($id)
    {
        $brief = MarketingBrief::with('client')->findOrFail($id);
        return view('gel-communication.briefs.show', compact('brief'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $brief = MarketingBrief::findOrFail($id);
        $brief->update(['status' => $request->status]);

        if ($request->status === 'accepted') {
            // Auto-create a campaign
            MarketingCampaign::create([
                'client_id' => $brief->client_id,
                'name' => 'Campagne : ' . ucfirst(str_replace('_', ' ', $brief->type)),
                'type' => $brief->type,
                'status' => 'active',
                'budget' => $brief->budget_estimation,
                'start_date' => now(),
            ]);
            return redirect()->route('gel-communication.campaigns.index')->with('success', 'Brief accepté et campagne créée.');
        }

        return redirect()->route('gel-communication.briefs.index')->with('success', 'Statut du brief mis à jour.');
    }
}
