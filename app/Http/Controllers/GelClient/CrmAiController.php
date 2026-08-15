<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CompanyCrmDeal;
use App\Models\CompanyCrmContact;
use App\Services\IA\CrmAiService;

class CrmAiController extends Controller
{
    protected CrmAiService $crmAiService;

    public function __construct(CrmAiService $crmAiService)
    {
        $this->crmAiService = $crmAiService;
    }

    /**
     * Affiche la vue Kanban des affaires avec les scores IA.
     */
    public function kanban(Request $request)
    {
        // En pratique, on filtrerait par client_id (le tenant actif)
        // $clientId = session('current_client_id') ?? auth()->user()->company_id;
        // Ici on prend toutes les affaires pour l'exemple
        $deals = CompanyCrmDeal::with(['contact', 'interactions'])->get();
        
        $dealsData = $deals->map(function ($deal) {
            $aiAnalysis = $this->crmAiService->analyzeDeal($deal);
            
            return [
                'id' => $deal->id,
                'title' => $deal->title,
                'amount' => $deal->amount,
                'stage' => $deal->stage,
                'status' => $deal->status,
                'probability' => $deal->probability,
                'contact' => $deal->contact ? $deal->contact->first_name . ' ' . $deal->contact->last_name : 'Sans contact',
                'ai_score' => $aiAnalysis['score'],
                'ai_temperature' => $aiAnalysis['temperature'],
                'ai_color' => $aiAnalysis['color'],
                'ai_reasons' => $aiAnalysis['reasons'],
                'ai_next_action' => $aiAnalysis['next_action'],
            ];
        });

        // Organiser par colonne Kanban
        $columns = [
            'prospecting' => $dealsData->where('stage', 'prospecting')->values(),
            'qualification' => $dealsData->where('stage', 'qualification')->values(),
            'proposal' => $dealsData->where('stage', 'proposal')->values(),
            'negociation' => $dealsData->where('stage', 'negociation')->values(),
            'closing' => $dealsData->where('stage', 'closing')->values(),
        ];

        return Inertia::render('Modules/Crm/Deals/Kanban', [
            'columns' => $columns,
            'totalDeals' => $dealsData->count()
        ]);
    }

    /**
     * Affiche la fiche détaillée d'un contact avec les recommandations IA.
     */
    public function showContact(CompanyCrmContact $contact)
    {
        $contact->load(['deals', 'interactions']);
        
        $aiAnalysis = $this->crmAiService->analyzeContact($contact);

        return Inertia::render('Modules/Crm/Contacts/Show', [
            'contact' => $contact,
            'aiInsight' => $aiAnalysis['insight'],
            'aiAction' => $aiAnalysis['suggested_action'],
            'activeDealsCount' => $aiAnalysis['active_deals_count']
        ]);
    }
}
