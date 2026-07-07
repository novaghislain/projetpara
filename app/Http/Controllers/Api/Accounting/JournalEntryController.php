<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreJournalEntryRequest;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalEntryController extends Controller
{
    private JournalEntryService $entryService;

    public function __construct(JournalEntryService $entryService)
    {
        $this->entryService = $entryService;
    }

    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Liste des écritures comptables.
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();

        $query = JournalEntry::where('client_id', $clientId)
            ->with(['journal:id,code,label', 'creator:id,name']);

        // Filtres
        if ($request->filled('journal_id')) {
            $query->where('journal_id', $request->journal_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('entry_date', '<=', $request->date_to);
        }
        if ($request->filled('fiscal_period_id')) {
            $query->where('fiscal_period_id', $request->fiscal_period_id);
        }

        $entries = $query->orderBy('entry_date', 'desc')
            ->orderBy('entry_number', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $entries,
        ]);
    }

    /**
     * Créer une écriture comptable.
     */
    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        try {
            $entry = $this->entryService->createEntry($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Écriture créée avec succès.',
                'data' => $entry->load(['journal', 'lines', 'creator']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Afficher une écriture.
     */
    public function show(string $id): JsonResponse
    {
        $entry = JournalEntry::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->with([
                'journal',
                'lines' => fn ($q) => $q->orderBy('line_number'),
                'lines.account',
                'creator',
                'validator',
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $entry,
        ]);
    }

    /**
     * Valider (poster) une écriture.
     */
    public function post(string $id): JsonResponse
    {
        try {
            $entry = $this->entryService->postEntry($id);

            return response()->json([
                'success' => true,
                'message' => 'Écriture validée.',
                'data' => $entry,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Annuler une écriture.
     */
    public function cancel(string $id, Request $request): JsonResponse
    {
        try {
            $entry = $this->entryService->cancelEntry($id, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Écriture annulée.',
                'data' => $entry,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Supprimer une écriture (brouillon uniquement).
     */
    public function destroy(string $id): JsonResponse
    {
        $entry = JournalEntry::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->where('status', JournalEntry::STATUS_DRAFT)
            ->firstOrFail();

        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Écriture supprimée.',
        ]);
    }
}
