<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingClosingEntry;
use App\Models\FiscalYear;
use App\Services\Accounting\ClosingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClosingController extends Controller
{
    protected ClosingService $closingService;

    public function __construct(ClosingService $closingService)
    {
        $this->closingService = $closingService;
    }

    /**
     * Page des opérations de clôture.
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-accounting-closing',
            'clientId' => $clientId,
        ]);
    }

    /**
     * API: Liste des écritures de clôture.
     */
    public function listAll($clientId)
    {
        $entries = AccountingClosingEntry::where('client_id', $clientId)
            ->with(['fiscalYear', 'createdBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($entries);
    }

    /**
     * API: Détail d'une écriture de clôture.
     */
    public function show($clientId, $id)
    {
        $entry = AccountingClosingEntry::where('client_id', $clientId)
            ->with(['fiscalYear', 'journal.lines.account', 'createdBy:id,name', 'validatedBy:id,name'])
            ->findOrFail($id);

        return response()->json($entry);
    }

    /**
     * API: Exécuter la clôture d'un exercice.
     */
    public function cloturer(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ]);

        $result = $this->closingService->cloturerExercice(
            $validated['fiscal_year_id'],
            Auth::id()
        );

        return response()->json($result);
    }

    /**
     * API: Réouverture d'un exercice.
     */
    public function rouvrir(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ]);

        try {
            $fiscalYear = $this->closingService->rouvrirExercice(
                $validated['fiscal_year_id'],
                Auth::id()
            );
            return response()->json($fiscalYear);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    /**
     * API: Écriture d'inventaire.
     */
    public function inventaire(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'entries' => 'required|array|min:1',
            'entries.*.account_id' => 'required|exists:accounting_accounts,id',
            'entries.*.label' => 'required|string|max:500',
            'entries.*.debit' => 'nullable|numeric|min:0',
            'entries.*.credit' => 'nullable|numeric|min:0',
        ]);

        try {
            $entry = $this->closingService->ecritureInventaire(
                $validated['fiscal_year_id'],
                Auth::id(),
                $validated['entries']
            );
            return response()->json($entry, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    /**
     * API: Statistiques de clôture.
     */
    public function stats($clientId)
    {
        $years = FiscalYear::where('client_id', $clientId)
            ->orderBy('year', 'desc')
            ->get();

        $stats = $years->map(function ($year) {
            $closingCount = AccountingClosingEntry::where('fiscal_year_id', $year->id)->count();
            return [
                'year' => $year->year,
                'status' => $year->status,
                'closing_entries' => $closingCount,
                'date_start' => $year->date_start->format('Y-m-d'),
                'date_end' => $year->date_end->format('Y-m-d'),
                'check_balance' => $year->check_balance,
                'check_tva' => $year->check_tva,
                'check_cnss' => $year->check_cnss,
                'check_reconciliation' => $year->check_reconciliation,
            ];
        });

        return response()->json($stats);
    }
}
