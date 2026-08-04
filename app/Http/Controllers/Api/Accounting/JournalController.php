<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreJournalRequest;
use App\Http\Resources\JournalResource;
use App\Models\FiscalYear;
use App\Models\Journal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API des journaux comptables.
 *
 * Gère les opérations CRUD sur les journaux et la création
 * des 8 journaux par défaut selon le plan SYSCOHADA.
 */
class JournalController extends Controller
{
    /**
     * Récupère l'ID du client connecté.
     *
     * @return int
     */
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Liste des journaux du client connecté.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();

        $journals = Journal::where('client_id', $clientId)
            ->with('fiscalYear')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => JournalResource::collection($journals),
        ]);
    }

    /**
     * Créer un journal.
     *
     * @param  StoreJournalRequest  $request
     * @return JsonResponse
     */
    public function store(StoreJournalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['client_id'] = $this->getClientId();
        $data['is_default'] = false;
        $data['is_active'] = true;

        $journal = Journal::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Journal créé avec succès.',
            'data' => new JournalResource($journal),
        ], 201);
    }

    /**
     * Afficher un journal avec ses dernières écritures.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $journal = Journal::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->with([
                'fiscalYear',
                'entries' => fn ($q) => $q->orderBy('entry_date', 'desc')->limit(50),
                'entries.lines',
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $journal,
        ]);
    }

    /**
     * Modifier un journal.
     *
     * @param  StoreJournalRequest  $request
     * @param  string  $id
     * @return JsonResponse
     */
    public function update(StoreJournalRequest $request, string $id): JsonResponse
    {
        $journal = Journal::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        $journal->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Journal mis à jour.',
            'data' => new JournalResource($journal->fresh()),
        ]);
    }

    /**
     * Créer les 8 journaux par défaut SYSCOHADA pour un exercice.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function createDefaults(Request $request): JsonResponse
    {
        $clientId = $request->user()->client_id;
        $fiscalYearId = $request->input('fiscal_year_id');

        $fiscalYear = FiscalYear::where('id', $fiscalYearId)
            ->where('client_id', $clientId)
            ->firstOrFail();

        $defaults = Journal::TYPES;
        $created = [];

        foreach ($defaults as $type => $config) {
            $existing = Journal::where('client_id', $clientId)
                ->where('fiscal_year_id', $fiscalYearId)
                ->where('code', $config['code'])
                ->first();

            if (!$existing) {
                $journal = Journal::create([
                    'client_id'      => $clientId,
                    'fiscal_year_id' => $fiscalYearId,
                    'code'           => $config['code'],
                    'label'          => $config['label'],
                    'type'           => $type,
                    'prefix'         => $config['prefix'],
                    'is_default'     => true,
                    'is_active'      => true,
                    'next_number'    => 1,
                    'sort_order'     => count($created),
                ]);
                $created[] = $journal;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($created) . ' journaux par défaut créés.',
            'data' => JournalResource::collection($created),
        ]);
    }
}
