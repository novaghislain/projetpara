<?php

namespace App\Http\Controllers\Gel\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FiscalYear;
use App\Services\Comptabilite\WorkpaperService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkpaperController extends Controller
{
    protected $workpaperService;

    public function __construct(WorkpaperService $workpaperService)
    {
        $this->workpaperService = $workpaperService;
    }

    /**
     * Affiche l'interface principale des Workpapers (Balance de révision)
     */
    public function index(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        if (!$clientId) {
            abort(403, 'Aucun client actif sélectionné.');
        }
        
        $client = Client::findOrFail($clientId);
        // En conditions réelles, on récupère l'exercice actif.
        // Pour la démonstration, on récupère le dernier exercice ou on mock si inexistant.
        $fiscalYear = FiscalYear::where('client_id', $client->id)->orderBy('start_date', 'desc')->first();
        
        if (!$fiscalYear) {
            // Mock fallback s'il n'y a pas d'année fiscale pour ce client (démo)
            $fiscalYear = (object)[
                'id' => 1,
                'client_id' => $client->id,
                'name' => 'Exercice ' . date('Y'),
                'start_date' => date('Y-01-01'),
                'end_date' => date('Y-12-31'),
            ];
        }
        
        $period = $request->input('period', 'annual');

        $workpapers = $this->workpaperService->getOrInitializeWorkpapers($client, $fiscalYear, $period);
        $stats = $this->workpaperService->getProgressStats($client, $fiscalYear, $period);

        return Inertia::render('Gel/Accounting/Workpapers', [
            'client' => $client,
            'fiscalYear' => $fiscalYear,
            'period' => $period,
            'workpapers' => $workpapers,
            'stats' => $stats,
        ]);
    }

    /**
     * Met à jour le statut d'un compte révisé (API)
     */
    public function updateStatus(Request $request, int $workpaperId)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        if (!$clientId) {
            return response()->json(['error' => 'Aucun client sélectionné'], 403);
        }
        $request->validate([
            'status' => 'required|in:pending,reviewed,error',
            'notes' => 'nullable|string',
        ]);

        $reviewerId = 1; // Dans un cas réel, ce serait auth()->id()

        $workpaper = $this->workpaperService->updateStatus(
            $workpaperId,
            $request->input('status'),
            $reviewerId,
            $request->input('notes')
        );

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'workpaper' => $workpaper,
        ]);
    }
}
