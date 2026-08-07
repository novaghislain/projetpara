<?php

namespace App\Http\Controllers\GelCommunication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\MarketingCampaign;
use App\Models\Gel\MarketingStat;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = MarketingCampaign::with('client')->orderBy('created_at', 'desc')->paginate(15);
        return view('gel-communication.campaigns.index', compact('campaigns'));
    }

    public function show($id)
    {
        $campaign = MarketingCampaign::with(['client', 'contents', 'stats'])->findOrFail($id);
        return view('gel-communication.campaigns.show', compact('campaign'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string',
            'type' => 'required|string',
            'budget' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        MarketingCampaign::create($request->all());
        return redirect()->route('gel-communication.campaigns.index')->with('success', 'Campagne créée.');
    }
}
