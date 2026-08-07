<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\MarketingBrief;
use App\Models\Gel\MarketingCampaign;
use App\Models\Gel\MarketingContent;
use App\Models\Client;

class MarketingController extends Controller
{
    public function index()
    {
        $clientId = auth()->user()->active_client_id ?? auth()->user()->client_id;
        $campaigns = MarketingCampaign::with('stats')->where('client_id', $clientId)->get();
        $briefs = MarketingBrief::where('client_id', $clientId)->get();
        $pendingContents = MarketingContent::whereHas('campaign', function($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->where('status', 'pending_client')->get();

        return view('client.marketing.index', compact('campaigns', 'briefs', 'pendingContents'));
    }

    public function storeBrief(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'description' => 'required|string',
            'budget_estimation' => 'nullable|numeric'
        ]);

        $clientId = auth()->user()->active_client_id ?? auth()->user()->client_id;
        
        MarketingBrief::create([
            'client_id' => $clientId,
            'type' => $request->type,
            'description' => $request->description,
            'budget_estimation' => $request->budget_estimation,
        ]);

        return back()->with('success', 'Votre demande de prestation marketing a été envoyée.');
    }

    public function showCampaign($id)
    {
        $clientId = auth()->user()->active_client_id ?? auth()->user()->client_id;
        $campaign = MarketingCampaign::with(['contents', 'stats'])
            ->where('client_id', $clientId)
            ->findOrFail($id);

        return view('client.marketing.show', compact('campaign'));
    }
}
