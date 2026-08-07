<?php

namespace App\Http\Controllers\GelCommunication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\MarketingContent;
use App\Models\Gel\MarketingCampaign;

class ContentController extends Controller
{
    public function index()
    {
        return view('gel-communication.contents.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:marketing_campaigns,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'platform' => 'nullable|string',
        ]);

        $content = new MarketingContent($request->only(['campaign_id', 'title', 'description', 'publish_date', 'platform']));
        $content->status = 'pending_client'; // Auto-request validation

        // Dummy file path for now since it's a demo
        $content->file_path = '/assets/img/demo-visual.jpg';
        $content->save();

        return back()->with('success', 'Contenu ajouté et soumis pour validation.');
    }

    public function updateFeedback(Request $request, $id)
    {
        $content = MarketingContent::findOrFail($id);
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'client_feedback' => 'nullable|string',
        ]);

        $content->update([
            'status' => $request->status,
            'client_feedback' => $request->client_feedback
        ]);

        return back()->with('success', 'Retour pris en compte.');
    }
}
