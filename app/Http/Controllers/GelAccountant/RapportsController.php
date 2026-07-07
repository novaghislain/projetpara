<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\SavedReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapportsController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $savedReports = SavedReport::where('cabinet_id', $cabinetId)
            ->orWhere(function ($q) use ($cabinetId) {
                $q->whereNull('cabinet_id')->where('partage', true);
            })
            ->orderBy('nom')
            ->get();

        $stats = [
            'sauvegardes' => $savedReports->count(),
        ];

        return view('gel-accountant.rapports.index', compact('savedReports', 'stats'));
    }

    public function create()
    {
        return view('gel-accountant.rapports.create');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'client_id' => 'nullable|exists:gel_clients,id',
            'filtres' => 'nullable|json',
            'colonnes' => 'nullable|json',
            'configuration' => 'nullable|json',
            'partage' => 'boolean',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;

        SavedReport::create($validated);

        return redirect()->route('gel-accountant.rapports')
            ->with('success', 'Rapport sauvegardé.');
    }

    public function destroy($id)
    {
        $report = SavedReport::findOrFail($id);
        $report->delete();

        return redirect()->route('gel-accountant.rapports')
            ->with('success', 'Rapport supprimé.');
    }
}
