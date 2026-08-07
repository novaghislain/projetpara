<?php

namespace App\Http\Controllers\GelCommunication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\MarketingContent;
use App\Models\Gel\MarketingCampaign;
use Illuminate\Support\Facades\Response;

class CalendarController extends Controller
{
    public function index()
    {
        // 1. Fetch campaigns for the Agenda tab
        $campaignsList = MarketingCampaign::where('status', 'active')->get();
        $clients = \App\Models\Client::all();
        
        // 2. Fetch contents grouped by platform for the Platform tabs
        // We will order them by publish_date to display them sequentially
        $contents = MarketingContent::with('campaign')
            ->whereNotNull('publish_date')
            ->orderBy('publish_date', 'asc')
            ->get();
            
        // Group by platform
        $platformContents = $contents->groupBy(function($item) {
            return strtolower($item->platform ?: 'autre');
        });
        
        // Standardize platforms to match the tabs
        $platforms = ['facebook', 'linkedin', 'instagram', 'twitter', 'tiktok', 'site web'];
        foreach($platformContents as $key => $items) {
            if (!in_array($key, $platforms)) {
                $platforms[] = $key;
            }
        }

        return view('gel-communication.calendar.index', compact('clients', 'campaignsList', 'platformContents', 'platforms', 'contents'));
    }

    public function export()
    {
        $fileName = 'Calendrier_Editorial_Strategique_' . date('Y_m_d') . '.xls';

        $contents = MarketingContent::with('campaign')
            ->whereNotNull('publish_date')
            ->orderBy('publish_date', 'asc')
            ->get();
            
        // Group by platform
        $platformContents = $contents->groupBy(function($item) {
            return strtolower($item->platform ?: 'autre');
        });
        
        $platforms = ['facebook', 'linkedin', 'instagram', 'twitter', 'tiktok', 'site web'];
        foreach($platformContents as $key => $items) {
            if (!in_array($key, $platforms)) {
                $platforms[] = $key;
            }
        }

        $headers = array(
            "Content-type"        => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $html = view('gel-communication.calendar.export', compact('platformContents', 'platforms'))->render();

        return response($html, 200, $headers);
    }

    public function updateCell(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:marketing_contents,id',
            'field' => 'required|string',
            'value' => 'nullable|string'
        ]);

        $content = MarketingContent::find($request->id);
        
        $allowedFields = ['publish_date', 'theme', 'description', 'content_format', 'pillar', 'keywords', 'rsd', 'published_link'];
        if (in_array($request->field, $allowedFields)) {
            if ($request->field === 'publish_date') {
                $content->publish_date = \Carbon\Carbon::parse($request->value);
            } else {
                $content->{$request->field} = $request->value;
            }
            $content->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Field not allowed']);
    }

    public function storeQuick(Request $request)
    {
        $request->validate([
            'type' => 'required|in:campaign,content',
            'date' => 'required|date',
        ]);

        if ($request->type === 'campaign') {
            $request->validate([
                'name' => 'required|string',
                'client_id' => 'required|exists:clients,id'
            ]);
            
            MarketingCampaign::create([
                'client_id' => $request->client_id,
                'name' => $request->name,
                'type' => 'autre',
                'status' => 'active',
                'start_date' => $request->date,
                'end_date' => $request->date,
            ]);

            return back()->with('success', 'Campagne créée avec succès.');
        } else {
            $request->validate([
                'campaign_id' => 'required|exists:marketing_campaigns,id',
                'title' => 'required|string',
                'platform' => 'nullable|string',
                'theme' => 'nullable|string',
                'content_format' => 'nullable|string',
            ]);

            MarketingContent::create([
                'campaign_id' => $request->campaign_id,
                'title' => $request->title,
                'publish_date' => $request->date,
                'platform' => $request->platform,
                'theme' => $request->theme,
                'content_format' => $request->content_format,
                'status' => 'draft'
            ]);

            return back()->with('success', 'Contenu planifié avec succès.');
        }
    }
}
