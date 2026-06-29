<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyCrmContact;
use App\Models\CompanyCrmDeal;
use App\Models\CompanyCrmInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmController extends BaseCompanyController
{
    /**
     * Affiche la page CRM.
     */
    public function index()
    {
        $clientId = $this->getClientId();
        return view('company', ['page' => 'company-crm', 'clientId' => $clientId]);
    }

    // --- CONTACTS ------------------------------------------------------------------

    /**
     * API: Liste tous les contacts CRM de l'entreprise.
     */
    public function contacts()
    {
        $clientId = $this->getClientId();

        $contacts = CompanyCrmContact::byClient($clientId)
            ->withCount('deals', 'interactions')
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id'                 => $c->id,
                    'first_name'         => $c->first_name,
                    'last_name'          => $c->last_name,
                    'email'              => $c->email,
                    'phone'              => $c->phone,
                    'company'            => $c->company,
                    'position'           => $c->position,
                    'category'           => $c->category,
                    'notes'              => $c->notes,
                    'tags'               => $c->tags,
                    'deals_count'        => $c->deals_count,
                    'interactions_count' => $c->interactions_count,
                    'created_at'         => $c->created_at?->format('d/m/Y'),
                ];
            });

        return response()->json($contacts);
    }

    /**
     * API: Cree un contact CRM.
     */
    public function storeContact(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'company'    => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'category'   => 'required|in:client,partner,prospect,lead,supplier',
            'notes'      => 'nullable|string',
            'tags'       => 'nullable|array',
        ]);

        $contact = CompanyCrmContact::create(array_merge(
            $validated,
            ['client_id' => $clientId, 'created_by' => Auth::id()]
        ));

        return response()->json([
            'message' => 'Contact cree avec succes.',
            'contact' => $contact->fresh(),
        ], 201);
    }

    /**
     * API: Modifie un contact CRM.
     */
    public function updateContact(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $contact = CompanyCrmContact::byClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|max:255',
            'phone'      => 'sometimes|nullable|string|max:50',
            'company'    => 'sometimes|nullable|string|max:255',
            'position'   => 'sometimes|nullable|string|max:255',
            'category'   => 'sometimes|in:client,partner,prospect,lead,supplier',
            'notes'      => 'sometimes|nullable|string',
            'tags'       => 'sometimes|nullable|array',
        ]);

        $contact->update($validated);

        return response()->json([
            'message' => 'Contact mis a jour.',
            'contact' => $contact->fresh(),
        ]);
    }

    /**
     * API: Supprime un contact CRM.
     */
    public function destroyContact($id)
    {
        $clientId = $this->getClientId();

        $contact = CompanyCrmContact::byClient($clientId)->findOrFail($id);
        $contact->delete();

        return response()->json(['message' => 'Contact supprime.']);
    }

    // --- AFFAIRES (DEALS) -----------------------------------------------------------

    /**
     * API: Liste toutes les affaires de l'entreprise.
     */
    public function deals()
    {
        $clientId = $this->getClientId();

        $deals = CompanyCrmDeal::byClient($clientId)
            ->with(['contact:id,first_name,last_name,email,company'])
            ->withCount('interactions')
            ->latest()
            ->get()
            ->map(function ($d) {
                return [
                    'id'                  => $d->id,
                    'contact_id'          => $d->contact_id,
                    'contact_name'        => $d->contact ? $d->contact->first_name . ' ' . $d->contact->last_name : null,
                    'contact_email'       => $d->contact?->email,
                    'contact_company'     => $d->contact?->company,
                    'title'               => $d->title,
                    'description'         => $d->description,
                    'amount'              => (float) $d->amount,
                    'stage'               => $d->stage,
                    'status'              => $d->status,
                    'probability'         => $d->probability,
                    'expected_close_date' => $d->expected_close_date?->format('Y-m-d'),
                    'closed_at'           => $d->closed_at?->format('d/m/Y H:i'),
                    'notes'               => $d->notes,
                    'interactions_count'  => $d->interactions_count,
                    'created_at'          => $d->created_at?->format('d/m/Y'),
                ];
            });

        return response()->json($deals);
    }

    /**
     * API: Cree une affaire.
     */
    public function storeDeal(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'contact_id'          => 'nullable|exists:company_crm_contacts,id',
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'amount'              => 'required|numeric|min:0',
            'stage'               => 'required|in:prospection,qualification,proposition,negociation,finalise',
            'status'              => 'required|in:open,won,lost,abandoned',
            'probability'         => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'notes'               => 'nullable|string',
        ]);

        // Si un contact_id est fourni, verifier qu'il appartient bien au client
        if (!empty($validated['contact_id'])) {
            CompanyCrmContact::byClient($clientId)->findOrFail($validated['contact_id']);
        }

        $deal = CompanyCrmDeal::create(array_merge(
            $validated,
            [
                'client_id'   => $clientId,
                'probability' => $validated['probability'] ?? 50,
                'created_by'  => Auth::id(),
            ]
        ));

        return response()->json([
            'message' => 'Affaire creee avec succes.',
            'deal'    => $deal->fresh(['contact:id,first_name,last_name,email,company']),
        ], 201);
    }

    /**
     * API: Modifie une affaire.
     */
    public function updateDeal(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $deal = CompanyCrmDeal::byClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'contact_id'          => 'sometimes|nullable|exists:company_crm_contacts,id',
            'title'               => 'sometimes|string|max:255',
            'description'         => 'sometimes|nullable|string',
            'amount'              => 'sometimes|numeric|min:0',
            'stage'               => 'sometimes|in:prospection,qualification,proposition,negociation,finalise',
            'status'              => 'sometimes|in:open,won,lost,abandoned',
            'probability'         => 'sometimes|nullable|integer|min:0|max:100',
            'expected_close_date' => 'sometimes|nullable|date',
            'closed_at'           => 'sometimes|nullable|date',
            'notes'               => 'sometimes|nullable|string',
        ]);

        // Si le statut passe a won/lost/abandoned, enregistrer la date de cloture
        if (in_array($validated['status'] ?? $deal->status, ['won', 'lost', 'abandoned']) && !$deal->closed_at) {
            $validated['closed_at'] = now();
        }

        $deal->update($validated);

        return response()->json([
            'message' => 'Affaire mise a jour.',
            'deal'    => $deal->fresh(['contact:id,first_name,last_name,email,company']),
        ]);
    }

    /**
     * API: Supprime une affaire.
     */
    public function destroyDeal($id)
    {
        $clientId = $this->getClientId();

        $deal = CompanyCrmDeal::byClient($clientId)->findOrFail($id);
        $deal->delete();

        return response()->json(['message' => 'Affaire supprimee.']);
    }

    // --- INTERACTIONS ----------------------------------------------------------------

    /**
     * API: Liste toutes les interactions de l'entreprise.
     */
    public function interactions()
    {
        $clientId = $this->getClientId();

        $interactions = CompanyCrmInteraction::byClient($clientId)
            ->with([
                'contact:id,first_name,last_name,email',
                'deal:id,title,amount,stage',
            ])
            ->latest()
            ->get()
            ->map(function ($i) {
                return [
                    'id'           => $i->id,
                    'contact_id'   => $i->contact_id,
                    'contact_name' => $i->contact ? $i->contact->first_name . ' ' . $i->contact->last_name : null,
                    'contact_email' => $i->contact?->email,
                    'deal_id'      => $i->deal_id,
                    'deal_title'   => $i->deal?->title,
                    'deal_stage'   => $i->deal?->stage,
                    'type'         => $i->type,
                    'subject'      => $i->subject,
                    'description'  => $i->description,
                    'scheduled_at' => $i->scheduled_at?->format('Y-m-d H:i'),
                    'outcome'      => $i->outcome,
                    'created_at'   => $i->created_at?->format('d/m/Y H:i'),
                ];
            });

        return response()->json($interactions);
    }

    /**
     * API: Cree une interaction.
     */
    public function storeInteraction(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'contact_id'  => 'nullable|exists:company_crm_contacts,id',
            'deal_id'     => 'nullable|exists:company_crm_deals,id',
            'type'        => 'required|in:call,email,meeting,note,other',
            'subject'     => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at'=> 'nullable|date',
            'outcome'     => 'nullable|string|max:255',
        ]);

        // Verifier que le contact et le deal appartiennent bien au client
        if (!empty($validated['contact_id'])) {
            CompanyCrmContact::byClient($clientId)->findOrFail($validated['contact_id']);
        }
        if (!empty($validated['deal_id'])) {
            CompanyCrmDeal::byClient($clientId)->findOrFail($validated['deal_id']);
        }

        $interaction = CompanyCrmInteraction::create(array_merge(
            $validated,
            ['client_id' => $clientId, 'created_by' => Auth::id()]
        ));

        return response()->json([
            'message'     => 'Interaction enregistree.',
            'interaction' => $interaction->fresh([
                'contact:id,first_name,last_name,email',
                'deal:id,title,amount,stage',
            ]),
        ], 201);
    }

    // --- STATISTIQUES ----------------------------------------------------------------

    /**
     * API: Statistiques CRM.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        // Contacts
        $contacts = CompanyCrmContact::byClient($clientId)->get();
        $totalContacts = $contacts->count();
        $byCategory = $contacts->groupBy('category')->map(function ($group) {
            return $group->count();
        });

        // Affaires
        $deals = CompanyCrmDeal::byClient($clientId)->get();
        $totalDeals = $deals->count();
        $openDeals = $deals->where('status', 'open')->count();
        $wonDeals = $deals->where('status', 'won')->count();
        $lostDeals = $deals->where('status', 'lost')->count();

        $totalPipeline = $deals->whereIn('status', ['open'])->sum('amount');
        $totalWon = $deals->where('status', 'won')->sum('amount');

        $byStage = $deals->groupBy('stage')->map(function ($group) {
            return [
                'count'  => $group->count(),
                'amount' => (float) $group->sum('amount'),
            ];
        });

        // Interactions recentes (5 dernieres)
        $recentInteractions = CompanyCrmInteraction::byClient($clientId)
            ->with(['contact:id,first_name,last_name'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($i) {
                return [
                    'id'           => $i->id,
                    'contact_name' => $i->contact ? $i->contact->first_name . ' ' . $i->contact->last_name : null,
                    'type'         => $i->type,
                    'subject'      => $i->subject,
                    'created_at'   => $i->created_at?->format('d/m/Y H:i'),
                ];
            });

        // Taux de conversion (won / closed)
        $closedDeals = $wonDeals + $lostDeals;
        $conversionRate = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100) : 0;

        $stats = [
            'contacts' => [
                'total'      => $totalContacts,
                'by_category'=> $byCategory,
            ],
            'deals' => [
                'total'       => $totalDeals,
                'open'        => $openDeals,
                'won'         => $wonDeals,
                'lost'        => $lostDeals,
                'pipeline'    => (float) $totalPipeline,
                'won_amount'  => (float) $totalWon,
                'by_stage'    => $byStage,
                'conversion'  => $conversionRate,
            ],
            'recent_interactions' => $recentInteractions,
        ];

        return response()->json($stats);
    }
}
