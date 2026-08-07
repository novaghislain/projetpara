<?php

namespace App\Http\Controllers\GelCommunication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\MarketingCampaign;
use App\Models\Gel\MarketingContent;
use App\Models\Gel\MarketingBrief;
use App\Models\Gel\MarketingStat;

class DashboardController extends Controller
{
    public function index()
    {
        $activeCampaignsCount = MarketingCampaign::where('status', 'active')->count();
        $pendingBriefsCount = MarketingBrief::where('status', 'pending')->count();
        $pendingContentsCount = MarketingContent::where('status', 'pending_client')->count();

        // Get overall stats for active campaigns
        $totalSpend = MarketingStat::whereHas('campaign', function($q) {
            $q->where('status', 'active');
        })->sum('spend');

        $activeCampaigns = MarketingCampaign::with('client')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentBriefs = MarketingBrief::with('client')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('gel-communication.dashboard.index', compact(
            'activeCampaignsCount',
            'pendingBriefsCount',
            'pendingContentsCount',
            'totalSpend',
            'activeCampaigns',
            'recentBriefs'
        ));
    }
}
