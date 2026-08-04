<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\AccountingClosingEntry;
use App\Models\FiscalYear;
use App\Services\Accounting\ClosingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClosingController extends BaseGelAccountingController
{
    /**
     * Contrôleur de gestion des clôtures d'exercice comptable.
     * Permet d'exécuter la clôture, la réouverture et les écritures
     * d'inventaire pour les exercices fiscaux des clients.
     */

    protected ClosingService $closingService;

    /**
     * Injection du service de clôture comptable.
     *
     * @param ClosingService $closingService Service de gestion des clôtures
     */
    public function __construct(ClosingService $closingService)
    {
        $this->closingService = $closingService;
    }

    /**
     * Page des opérations de clôture.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\View\View
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-accounting-closing',
            'clientId' => $clientId,
        ]);
    }

    /**
     * API : Liste des écritures de clôture.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse La liste des écritures de clôture
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
     * API : Détail d'une écriture de clôture.
     *
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant de l'écriture
     * @return \Illuminate\Http\JsonResponse L'écriture avec ses relations
     */
    public function show($clientId, $id)
    {
        $entry = AccountingClosingEntry::where('client_id', $clientId)
            ->with(['fiscalYear', 'journal.lines.account', 'createdBy:id,name', 'validatedBy:id,name'])
            ->findOrFail($id);

        return response()->json($entry);
    }

    /**
     * API : Exécute la clôture d'un exercice fiscal.
     *
     * @param Request $request La requête HTTP avec l'identifiant de l'exercice
     * @return \Illuminate\Http\JsonResponse Le résultat de la clôture
     */
    public function cloturer(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ]);

        // Vérification que le client a accès à cet exercice
        if ($request->filled('client_id')) {
            $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
            if ((int) $fiscalYear->client_id !== (int) $request->input('client_id')) {
                abort(403, 'Accès non autorisé à cet exercice.');
            }
        }

        $result = $this->closingService->cloturerExercice(
            $validated['fiscal_year_id'],
            Auth::id()
        );

        return response()->json($result);
    }

    /**
     * API : Réouverture d'un exercice fiscal.
     *
     * @param Request $request La requête HTTP avec l'identifiant de l'exercice
     * @return \Illuminate\Http\JsonResponse L'exercice rouvert
     */
    public function rouvrir(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ]);

        // Vérification que le client a accès à cet exercice
        if ($request->filled('client_id')) {
            $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
            if ((int) $fiscalYear->client_id !== (int) $request->input('client_id')) {
                abort(403, 'Accès non autorisé à cet exercice.');
            }
        }

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
     * API : Crée une écriture d'inventaire.
     *
     * @param Request $request La requête HTTP avec les lignes d'inventaire
     * @return \Illuminate\Http\JsonResponse L'écriture d'inventaire créée
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

        // Vérification que le client a accès à cet exercice
        if ($request->filled('client_id')) {
            $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
            if ((int) $fiscalYear->client_id !== (int) $request->input('client_id')) {
                abort(403, 'Accès non autorisé à cet exercice.');
            }
        }

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
     * API : Statistiques de clôture par exercice fiscal.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse Les statistiques de clôture
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
                'closing_entries' => $closingCount,       // Nombre d'écritures de clôture
                'date_start' => $year->date_start->format('Y-m-d'),
                'date_end' => $year->date_end->format('Y-m-d'),
                'check_balance' => $year->check_balance,       // Vérification de l'équilibre
                'check_tva' => $year->check_tva,               // Vérification TVA
                'check_cnss' => $year->check_cnss,             // Vérification CNSS
                'check_reconciliation' => $year->check_reconciliation, // Vérification rapprochement
                'closed_at' => $year->closed_at?->format('Y-m-d'),
            ];
        });

        return response()->json($stats);
    }
}
