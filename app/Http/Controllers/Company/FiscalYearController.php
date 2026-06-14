<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FiscalYearController extends Controller
{
    private function getClientId()
    {
        $user = Auth::user();
        if (!$user->client_id) abort(403, 'Aucune entreprise associée.');
        return $user->client_id;
    }

    /**
     * Liste des exercices.
     */
    public function index()
    {
        $clientId = $this->getClientId();

        $years = FiscalYear::where('client_id', $clientId)
            ->with('closedBy')
            ->orderBy('year', 'desc')
            ->get()
            ->map(function ($fy) {
                return [
                    'id'         => $fy->id,
                    'year'       => $fy->year,
                    'date_start' => $fy->date_start?->format('Y-m-d'),
                    'date_end'   => $fy->date_end?->format('Y-m-d'),
                    'status'     => $fy->status,
                    'closed_at'  => $fy->closed_at?->format('d/m/Y H:i'),
                    'closed_by'  => $fy->closedBy?->name,
                    'checks'     => [
                        'balance'        => $fy->check_balance,
                        'tva'            => $fy->check_tva,
                        'cnss'           => $fy->check_cnss,
                        'reconciliation' => $fy->check_reconciliation,
                        'inventory'      => $fy->check_inventory,
                    ],
                    'notes' => $fy->notes,
                ];
            });

        return response()->json($years);
    }

    /**
     * Crée un exercice.
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'year'       => 'required|integer|min:2000|max:2100',
            'date_start' => 'required|date',
            'date_end'   => 'required|date|after:date_start',
            'notes'      => 'nullable|string',
        ]);

        $exists = FiscalYear::where('client_id', $clientId)
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Cet exercice existe déjà.'], 422);
        }

        $fy = FiscalYear::create([
            'client_id'  => $clientId,
            'year'       => $validated['year'],
            'date_start' => $validated['date_start'],
            'date_end'   => $validated['date_end'],
            'status'     => 'open',
            'notes'      => $validated['notes'] ?? null,
        ]);

        return response()->json(['message' => 'Exercice créé.', 'fiscal_year' => $fy], 201);
    }

    /**
     * Affiche un exercice.
     */
    public function show($id)
    {
        $clientId = $this->getClientId();
        $fy = FiscalYear::where('client_id', $clientId)->with('closedBy')->findOrFail($id);
        return response()->json(['fiscal_year' => $fy]);
    }

    /**
     * Met à jour les notes.
     */
    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $fy = FiscalYear::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $fy->update($validated);

        return response()->json(['message' => 'Exercice mis à jour.', 'fiscal_year' => $fy->fresh()]);
    }

    /**
     * Clôture d'exercice avec checklist.
     */
    public function close(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $fy = FiscalYear::where('client_id', $clientId)->where('status', 'open')->findOrFail($id);

        $validated = $request->validate([
            'check_balance'        => 'required|boolean',
            'check_tva'            => 'required|boolean',
            'check_cnss'           => 'required|boolean',
            'check_reconciliation' => 'required|boolean',
            'check_inventory'      => 'required|boolean',
            'notes'                => 'nullable|string',
        ]);

        $fy->update([
            'status'               => 'closed',
            'closed_at'            => now(),
            'closed_by'            => Auth::id(),
            'check_balance'        => $validated['check_balance'],
            'check_tva'            => $validated['check_tva'],
            'check_cnss'           => $validated['check_cnss'],
            'check_reconciliation' => $validated['check_reconciliation'],
            'check_inventory'      => $validated['check_inventory'],
            'notes'                => $validated['notes'] ?? $fy->notes,
        ]);

        AuditTrailService::log($fy, 'closed', null, $fy->toArray(), 'Exercice comptable clôturé');

        return response()->json(['message' => 'Exercice clôturé.', 'fiscal_year' => $fy->fresh()]);
    }

    /**
     * Réouvre un exercice clôturé.
     */
    public function reopen($id)
    {
        $clientId = $this->getClientId();
        $fy = FiscalYear::where('client_id', $clientId)->where('status', 'closed')->findOrFail($id);

        $fy->update([
            'status'    => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);

        AuditTrailService::log($fy, 'reopened', null, $fy->toArray(), 'Exercice comptable réouvert');

        return response()->json(['message' => 'Exercice réouvert.', 'fiscal_year' => $fy->fresh()]);
    }

    /**
     * Verrouille un exercice (empêche toute modification).
     */
    public function lock($id)
    {
        $clientId = $this->getClientId();
        $fy = FiscalYear::where('client_id', $clientId)->findOrFail($id);
        $fy->update(['status' => 'locked']);

        return response()->json(['message' => 'Exercice verrouillé.', 'fiscal_year' => $fy->fresh()]);
    }

    /**
     * Supprime un exercice (seulement si aucun journal lié).
     */
    public function destroy($id)
    {
        $clientId = $this->getClientId();
        $fy = FiscalYear::where('client_id', $clientId)->findOrFail($id);

        if ($fy->journals()->exists()) {
            return response()->json(['message' => 'Impossible : des journaux sont liés à cet exercice.'], 422);
        }

        $fy->delete();
        return response()->json(['message' => 'Exercice supprimé.']);
    }
}
