<?php

namespace App\Http\Controllers\Company\Compta;

use App\Http\Controllers\Controller;
use App\Models\Compta\Ecriture;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\Compte;
use App\Services\Accounting\DoubleEntryValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EcritureController extends Controller
{
    public function __construct(
        private DoubleEntryValidatorService $validator
    ) {}

    public function index(Request $request)
    {
        $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;

        if (!$clientId) {
            return redirect()->route('select.context')
                ->withErrors(['Aucune entreprise associée.']);
        }

        return view('company', [
            'page' => 'compta-ecritures',
            'clientId' => $clientId,
        ]);
    }

    public function show($id)
    {
        $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        $ecriture = Ecriture::where('client_id', $clientId)
            ->with(['journal', 'lignes.compte', 'validateur'])
            ->findOrFail($id);

        return response()->json(['ecriture' => $ecriture]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'date_ecriture' => 'required|date',
            'libelle' => 'required|string|max:255',
            'lignes' => 'required|array|min:2',
            'lignes.*.compte_id' => 'required|exists:comptes,id',
            'lignes.*.debit' => 'required|numeric|min:0',
            'lignes.*.credit' => 'required|numeric|min:0',
        ]);

        $clientId = Auth::user()->active_client_id;

        // Validation partie double
        $this->validator->validate($request->lignes);

        DB::transaction(function () use ($request, $clientId) {
            $totalDebit = collect($request->lignes)->sum('debit');
            $totalCredit = collect($request->lignes)->sum('credit');

            // Générer une référence unique
            $reference = $this->generateReference($clientId, $request->journal_id);

            $ecriture = Ecriture::create([
                'client_id' => $clientId,
                'journal_id' => $request->journal_id,
                'exercice_id' => $request->exercice_id ?? null,
                'date_ecriture' => $request->date_ecriture,
                'libelle' => $request->libelle,
                'numero_piece' => $request->numero_piece,
                'reference' => $reference,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'piece_jointe_path' => $request->piece_jointe_path,
            ]);

            foreach ($request->lignes as $ligne) {
                LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $ligne['compte_id'],
                    'libelle' => $ligne['libelle'] ?? $request->libelle,
                    'debit' => $ligne['debit'],
                    'credit' => $ligne['credit'],
                ]);

                // Mettre à jour le solde du compte
                $compte = Compte::findOrFail($ligne['compte_id']);
                $compte->increment('solde_debiteur', $ligne['debit']);
                $compte->increment('solde_crediteur', $ligne['credit']);
            }
        });

        return back()->with('success', 'Écriture comptable enregistrée.');
    }

    public function update(Request $request, $id)
    {
        $clientId = Auth::user()->active_client_id;
        $ecriture = Ecriture::where('client_id', $clientId)->findOrFail($id);

        if ($ecriture->is_validee) {
            return back()->withErrors(['error' => 'Impossible de modifier une écriture validée.']);
        }

        $request->validate([
            'libelle' => 'required|string|max:255',
            'lignes' => 'required|array|min:2',
        ]);

        $this->validator->validate($request->lignes);

        DB::transaction(function () use ($request, $ecriture) {
            // Annuler les anciens soldes
            foreach ($ecriture->lignes as $ligne) {
                $compte = $ligne->compte;
                $compte->decrement('solde_debiteur', $ligne->debit);
                $compte->decrement('solde_crediteur', $ligne->credit);
            }
            $ecriture->lignes()->delete();

            $totalDebit = collect($request->lignes)->sum('debit');
            $totalCredit = collect($request->lignes)->sum('credit');

            $ecriture->update([
                'libelle' => $request->libelle,
                'date_ecriture' => $request->date_ecriture ?? $ecriture->date_ecriture,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            foreach ($request->lignes as $ligne) {
                LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $ligne['compte_id'],
                    'libelle' => $ligne['libelle'] ?? $ecriture->libelle,
                    'debit' => $ligne['debit'],
                    'credit' => $ligne['credit'],
                ]);
                $compte = Compte::findOrFail($ligne['compte_id']);
                $compte->increment('solde_debiteur', $ligne['debit']);
                $compte->increment('solde_crediteur', $ligne['credit']);
            }
        });

        return back()->with('success', 'Écriture mise à jour.');
    }

    public function destroy($id)
    {
        $clientId = Auth::user()->active_client_id;
        $ecriture = Ecriture::where('client_id', $clientId)->findOrFail($id);

        if ($ecriture->is_validee) {
            return back()->withErrors(['error' => 'Impossible de supprimer une écriture validée.']);
        }

        DB::transaction(function () use ($ecriture) {
            foreach ($ecriture->lignes as $ligne) {
                $compte = $ligne->compte;
                $compte->decrement('solde_debiteur', $ligne->debit);
                $compte->decrement('solde_crediteur', $ligne->credit);
            }
            $ecriture->lignes()->delete();
            $ecriture->delete();
        });

        return back()->with('success', 'Écriture supprimée.');
    }

    public function validateEntry(Request $request, $id)
    {
        $clientId = Auth::user()->active_client_id;
        $ecriture = Ecriture::where('client_id', $clientId)->findOrFail($id);

        if ($ecriture->is_validee) {
            return back()->withErrors(['error' => 'Écriture déjà validée.']);
        }

        $ecriture->update([
            'is_validee' => true,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return back()->with('success', 'Écriture validée. Elle ne peut plus être modifiée.');
    }

    public function grandLivre(Request $request)
    {
        $clientId = Auth::user()->active_client_id;

        $request->validate([
            'compte_id' => 'nullable|exists:comptes,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
        ]);

        $query = LigneEcriture::whereHas('ecriture', fn($q) => $q->where('client_id', $clientId))
            ->with(['compte', 'ecriture.journal']);

        if ($request->filled('compte_id')) {
            $query->where('compte_id', $request->compte_id);
        }
        if ($request->filled('date_debut')) {
            $query->whereHas('ecriture', fn($q) => $q->where('date_ecriture', '>=', $request->date_debut));
        }
        if ($request->filled('date_fin')) {
            $query->whereHas('ecriture', fn($q) => $q->where('date_ecriture', '<=', $request->date_fin));
        }

        $lignes = $query->orderBy('created_at')->paginate(100)->appends($request->all());

        return response()->json([
            'lignes' => $lignes->items(),
            'filters' => $request->only(['compte_id', 'date_debut', 'date_fin']),
            'paginator' => [
                'current_page' => $lignes->currentPage(),
                'last_page' => $lignes->lastPage(),
                'total' => $lignes->total(),
            ],
        ]);
    }

    public function balance(Request $request)
    {
        $clientId = Auth::user()->active_client_id;

        $comptes = Compte::where('client_id', $clientId)
            ->where('is_actif', true)
            ->orderBy('numero')
            ->get(['id', 'numero', 'intitule', 'classe', 'type', 'solde_debiteur', 'solde_crediteur']);

        $balance = $comptes->map(function ($compte) {
            $solde = $compte->solde_debiteur - $compte->solde_crediteur;
            return [
                'numero' => $compte->numero,
                'intitule' => $compte->intitule,
                'classe' => $compte->classe,
                'type' => $compte->type,
                'total_debit' => $compte->solde_debiteur,
                'total_credit' => $compte->solde_crediteur,
                'solde_debiteur' => $solde > 0 ? $solde : 0,
                'solde_crediteur' => $solde < 0 ? abs($solde) : 0,
            ];
        });

        return response()->json([
            'balance' => $balance,
            'totaux' => [
                'total_debit' => $balance->sum('total_debit'),
                'total_credit' => $balance->sum('total_credit'),
                'solde_debiteur' => $balance->sum('solde_debiteur'),
                'solde_crediteur' => $balance->sum('solde_crediteur'),
            ],
        ]);
    }

    private function generateReference(int $clientId, int $journalId): string
    {
        $journal = \App\Models\Compta\Journal::find($journalId);
        $year = date('Y');
        $count = Ecriture::where('client_id', $clientId)
            ->where('journal_id', $journalId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return strtoupper($journal->code) . '-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
