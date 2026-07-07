<?php

namespace App\Http\Controllers\Gel\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\Comptabilite\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    /**
     * Liste des journaux du cabinet.
     */
    public function index(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;

        $journaux = Journal::byCabinet($cabinetId)
            ->withCount('ecritures')
            ->orderBy('code')
            ->get()
            ->map(function ($journal) {
                $journal->total_debit = $journal->ecritures()
                    ->where('valide', true)
                    ->sum('total_debit');
                $journal->total_credit = $journal->ecritures()
                    ->where('valide', true)
                    ->sum('total_credit');
                return $journal;
            });

        if ($request->wantsJson()) {
            return response()->json($journaux);
        }

        return view('gel.comptabilite.journaux.index', compact('journaux'));
    }

    /**
     * Vue de création d'un journal.
     */
    public function create()
    {
        $codesExistants = Journal::where('cabinet_id', Auth::user()->cabinet_id)
            ->pluck('code')
            ->toArray();

        $codesDisponibles = [
            'AC' => 'Achats',
            'VE' => 'Ventes',
            'BN' => 'Banque',
            'CA' => 'Caisse',
            'OD' => 'Opérations diverses',
            'AN' => 'Engagements hors bilan',
            'SA' => 'Salaires',
            'IM' => 'Immobilisations',
        ];

        foreach ($codesExistants as $code) {
            unset($codesDisponibles[$code]);
        }

        return view('gel.comptabilite.journaux.create', compact('codesDisponibles'));
    }

    /**
     * Enregistre un nouveau journal.
     */
    public function store(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:5',
                function ($attr, $value, $fail) use ($cabinetId) {
                    $exists = Journal::where('cabinet_id', $cabinetId)
                        ->where('code', $value)->exists();
                    if ($exists) $fail('Ce code journal existe déjà.');
                },
            ],
            'libelle' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'actif' => 'nullable|boolean',
        ]);

        $journal = Journal::create([
            'cabinet_id' => $cabinetId,
            'code' => $validated['code'],
            'libelle' => $validated['libelle'],
            'type' => $validated['type'] ?? null,
            'actif' => $validated['actif'] ?? true,
        ]);

        if ($request->wantsJson()) {
            return response()->json($journal, 201);
        }

        return redirect()->route('gel.comptabilite.journaux.index')
            ->with('success', "Journal {$journal->code} — {$journal->libelle} créé.");
    }

    /**
     * Détail d'un journal avec ses écritures.
     */
    public function show(Request $request, $id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $journal = Journal::byCabinet($cabinetId)
            ->withCount('ecritures')
            ->findOrFail($id);

        $ecritures = $journal->ecritures()
            ->with(['exercice', 'client', 'validateur'])
            ->orderBy('date_ecriture', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'journal' => $journal,
                'ecritures' => $ecritures,
            ]);
        }

        return view('gel.comptabilite.journaux.show', compact('journal', 'ecritures'));
    }

    /**
     * Vue d'édition.
     */
    public function edit($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $journal = Journal::byCabinet($cabinetId)->findOrFail($id);

        return view('gel.comptabilite.journaux.edit', compact('journal'));
    }

    /**
     * Met à jour un journal.
     */
    public function update(Request $request, $id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $journal = Journal::byCabinet($cabinetId)->findOrFail($id);

        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'actif' => 'nullable|boolean',
        ]);

        $journal->update($validated);

        if ($request->wantsJson()) {
            return response()->json($journal);
        }

        return redirect()->route('gel.comptabilite.journaux.index')
            ->with('success', "Journal {$journal->code} mis à jour.");
    }

    /**
     * Supprime un journal (uniquement s'il n'a pas d'écritures).
     */
    public function destroy($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $journal = Journal::byCabinet($cabinetId)->findOrFail($id);

        if ($journal->ecritures()->exists()) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Impossible de supprimer : le journal contient des écritures.'], 409);
            }
            return back()->withErrors(['message' => 'Impossible de supprimer : le journal contient des écritures.']);
        }

        $journal->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Journal supprimé.']);
        }

        return redirect()->route('gel.comptabilite.journaux.index')
            ->with('success', 'Journal supprimé.');
    }

    /**
     * Génère les 8 journaux standards SYSCOHADA.
     */
    public function createDefaults(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;

        $existing = Journal::where('cabinet_id', $cabinetId)->count();
        if ($existing > 0) {
            return response()->json(['message' => 'Des journaux existent déjà pour ce cabinet.'], 409);
        }

        $journaux = [
            ['code' => 'AC', 'libelle' => 'Journal des achats',                  'type' => 'achats'],
            ['code' => 'VE', 'libelle' => 'Journal des ventes',                  'type' => 'ventes'],
            ['code' => 'BN', 'libelle' => 'Journal des banques',                 'type' => 'banque'],
            ['code' => 'CA', 'libelle' => 'Journal de caisse',                   'type' => 'caisse'],
            ['code' => 'OD', 'libelle' => 'Journal des opérations diverses',     'type' => 'divers'],
            ['code' => 'AN', 'libelle' => 'Journal des engagements hors bilan',  'type' => 'hors-bilan'],
            ['code' => 'SA', 'libelle' => 'Journal des salaires',                'type' => 'paie'],
            ['code' => 'IM', 'libelle' => 'Journal des immobilisations',         'type' => 'immobilisations'],
        ];

        foreach ($journaux as $data) {
            Journal::create(array_merge($data, ['cabinet_id' => $cabinetId, 'actif' => true]));
        }

        return response()->json(['message' => '8 journaux standards créés.', 'count' => 8], 201);
    }
}
