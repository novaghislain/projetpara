<?php

namespace App\Http\Controllers\GelAccountant\Fiscalite;

use App\Http\Controllers\Controller;
use App\Models\AccountingJournalLine;
use App\Models\FiscalYear;
use App\Models\TvaDeclaration;
use App\Services\AuditTrailService;
use App\Services\FiscalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TvaController extends Controller
{
    /**
     * Affiche la liste des déclarations TVA.
     */
    public function index(Request $request)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $declarations = TvaDeclaration::where('client_id', $clientId)
            ->with('fiscalYear', 'createdBy')
            ->orderBy('period', 'desc')
            ->get();

        return view('gel-accountant.fiscalite.tva.index', compact('declarations') + [
            'currentSection' => 'fiscalite',
            'currentPage' => 'tva'
        ]);
    }

    /**
     * Affiche le formulaire pour créer/calculer une nouvelle déclaration.
     */
    public function create(Request $request)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $result = null;
        $period = $request->query('period'); // Format: YYYY-MM

        if ($period) {
            $validated = $request->validate([
                'period' => 'regex:/^\d{4}-\d{2}$/'
            ]);

            [$year, $month] = explode('-', $validated['period']);

            // Récupérer les lignes contenant de la TVA pour la période
            $lines = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId, $year, $month) {
                $q->where('client_id', $clientId)
                  ->where('status', 'posted')
                  ->whereYear('entry_date', $year)
                  ->whereMonth('entry_date', $month);
            })->whereNotNull('tva_code')->get();

            // Utilise le service existant
            $result = FiscalService::computeTvaDeclaration($lines);
            $result['period'] = $period;
        }

        return view('gel-accountant.fiscalite.tva.create', compact('result', 'period') + [
            'currentSection' => 'fiscalite',
            'currentPage' => 'tva'
        ]);
    }

    /**
     * Sauvegarde la déclaration TVA.
     */
    public function store(Request $request)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $validated = $request->validate([
            'period'         => 'required|regex:/^\d{4}-\d{2}$/',
            'tva_collected'  => 'required|numeric|min:0',
            'tva_deductible' => 'required|numeric|min:0',
            'tva_net'        => 'required|numeric',
        ]);

        $exists = TvaDeclaration::where('client_id', $clientId)
            ->where('period', $validated['period'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Une déclaration existe déjà pour cette période.');
        }

        // Trouver l'exercice fiscal correspondant
        [$year, $month] = explode('-', $validated['period']);
        $date = $year . '-' . $month . '-01';
        $fiscalYear = FiscalYear::where('client_id', $clientId)
            ->where('date_start', '<=', $date)
            ->where('date_end', '>=', $date)
            ->first();

        TvaDeclaration::create([
            'client_id'      => $clientId,
            'fiscal_year_id' => $fiscalYear ? $fiscalYear->id : null,
            'period'         => $validated['period'],
            'type'           => 'monthly',
            'tva_collected'  => $validated['tva_collected'],
            'tva_deductible' => $validated['tva_deductible'],
            'tva_net'        => $validated['tva_net'],
            'status'         => 'draft',
            'created_by'     => Auth::id(),
        ]);

        return redirect()->route('gel-accountant.fiscalite.tva.index')->with('success', 'Déclaration TVA enregistrée en brouillon.');
    }
    
    /**
     * Soumet la déclaration.
     */
    public function submit($id)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $d = TvaDeclaration::where('client_id', $clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $d->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        AuditTrailService::log($d, 'submitted', null, $d->toArray(), 'Déclaration TVA soumise par l\'expert-comptable');

        return redirect()->route('gel-accountant.fiscalite.tva.index')->with('success', 'Déclaration TVA validée et soumise.');
    }
}
