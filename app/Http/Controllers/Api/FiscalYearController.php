<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FiscalYearController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Liste des exercices comptables.
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();

        $years = FiscalYear::where('client_id', $clientId)
            ->with('closedBy:id,name')
            ->orderBy('year', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json($years);
    }

    /**
     * Détail d'un exercice.
     */
    public function show(int $id): JsonResponse
    {
        $clientId = $this->getClientId();
        $year = FiscalYear::where('client_id', $clientId)
            ->with(['closedBy:id,name', 'periods'])
            ->findOrFail($id);

        return response()->json($year);
    }

    /**
     * Créer un exercice.
     */
    public function store(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100', Rule::unique('fiscal_years', 'year')
                ->where(fn ($q) => $q->where('client_id', $clientId))],
            'date_start' => ['required', 'date'],
            'date_end' => ['required', 'date', 'after:date_start'],
            'notes' => ['nullable', 'string'],
        ]);

        $existing = FiscalYear::where('client_id', $clientId)
            ->where('status', 'open')
           ->first();

        $fiscalYear = DB::transaction(function () use ($clientId, $validated, $existing) {
            $year = FiscalYear::create([
                'client_id' => $clientId,
                'year' => $validated['year'],
                'date_start' => $validated['date_start'],
                'date_end' => $validated['date_end'],
                'status' => $existing ? 'open' : 'open',
                'notes' => $validated['notes'] ?? null,
            ]);

            return $year;
        });

        return response()->json([
            'message' => 'Exercice créé avec succès.',
            'fiscal_year' => $fiscalYear,
        ], 201);
    }

    /**
     * Modifier un exercice.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $clientId = $this->getClientId();
        $fiscalYear = FiscalYear::where('client_id', $clientId)->findOrFail($id);

        if ($fiscalYear->isClosed()) {
            return response()->json(['message' => 'Impossible de modifier un exercice clôturé.'], 409);
        }

        $validated = $request->validate([
            'year' => ['integer', 'min:2000', 'max:2100', Rule::unique('fiscal_years', 'year')
                ->ignore($id)->where(fn ($q) => $q->where('client_id', $clientId))],
            'date_start' => ['date'],
            'date_end' => ['date', 'after:date_start'],
            'notes' => ['nullable', 'string'],
        ]);

        $fiscalYear->update($validated);

        return response()->json([
            'message' => 'Exercice mis à jour.',
            'fiscal_year' => $fiscalYear->fresh(),
        ]);
    }

    /**
     * Clôturer un exercice.
     */
    public function close(int $id): JsonResponse
    {
        $clientId = $this->getClientId();
        $fiscalYear = FiscalYear::where('client_id', $clientId)->findOrFail($id);

        if ($fiscalYear->isClosed()) {
            return response()->json(['message' => 'Exercice déjà clôturé.'], 409);
        }

        DB::transaction(function () use ($fiscalYear) {
            $fiscalYear->status = 'closed';
            $fiscalYear->closed_at = now();
            $fiscalYear->closed_by = Auth::id();
            $fiscalYear->save();
        });

        return response()->json([
            'message' => 'Exercice clôturé avec succès.',
            'fiscal_year' => $fiscalYear->fresh(),
        ]);
    }

    /**
     * Supprimer un exercice.
     */
    public function destroy(int $id): JsonResponse
    {
        $clientId = $this->getClientId();
        $fiscalYear = FiscalYear::where('client_id', $clientId)->findOrFail($id);

        if (!$fiscalYear->isOpen()) {
            return response()->json(['message' => 'Seul un exercice ouvert peut être supprimé.'], 409);
        }

        if ($fiscalYear->journals()->count() > 0) {
            return response()->json(['message' => 'L\'exercice contient des écritures. Supprimez-les d\'abord.'], 409);
        }

        $fiscalYear->delete();

        return response()->json(['message' => 'Exercice supprimé.']);
    }
}
