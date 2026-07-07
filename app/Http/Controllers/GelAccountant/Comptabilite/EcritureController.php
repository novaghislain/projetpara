<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\EcritureComptable;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\Journal;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EcritureController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = EcritureComptable::where('cabinet_id', $cabinetId)
            ->with(['journal:id,code', 'client:id,nom_entreprise', 'lignes']);

        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }
        if ($journalId = $request->input('journal_id')) {
            $query->where('journal_id', $journalId);
        }
        if ($dateFrom = $request->input('date_from')) {
            $query->where('date_ecriture', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->where('date_ecriture', '<=', $dateTo);
        }
        if ($statut = $request->input('statut')) {
            $query->where('valide', $statut === 'valide');
        }

        $ecritures = $query->orderBy('date_ecriture', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $totalDebit = $ecritures->sum('total_debit');
        $totalCredit = $ecritures->sum('total_credit');

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);
        $journaux = Journal::where('cabinet_id', $cabinetId)->actif()->get(['id', 'code', 'libelle']);

        return view('gel-accountant.comptabilite.ecritures.index', compact(
            'ecritures', 'totalDebit', 'totalCredit', 'clients', 'journaux'
        ));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);
        $journaux = Journal::where('cabinet_id', $cabinetId)->actif()->get(['id', 'code', 'libelle']);
        $comptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'intitule']);

        return view('gel-accountant.comptabilite.ecritures.create', compact('clients', 'journaux', 'comptes'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'client_id' => 'nullable|exists:gel_clients,id',
            'journal_id' => 'required|exists:gel_journaux,id',
            'date_ecriture' => 'required|date',
            'date_piece' => 'nullable|date',
            'ref_piece' => 'nullable|string|max:100',
            'libelle' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'compte_id' => 'required|array',
            'compte_id.*' => 'exists:gel_comptes_comptables,id',
            'debit' => 'required|array',
            'debit.*' => 'nullable|integer|min:0',
            'credit' => 'required|array',
            'credit.*' => 'nullable|integer|min:0',
            'libelle_ligne' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $totalDebit = 0;
            $totalCredit = 0;
            $lignes = [];

            foreach ($request->compte_id as $i => $compteId) {
                $debit = (int)($request->debit[$i] ?? 0);
                $credit = (int)($request->credit[$i] ?? 0);
                $totalDebit += $debit;
                $totalCredit += $credit;

                if ($debit > 0 || $credit > 0) {
                    $lignes[] = [
                        'compte_id' => $compteId,
                        'sens' => $debit > 0 ? 'debit' : 'credit',
                        'montant' => $debit > 0 ? $debit : $credit,
                        'libelle_ligne' => $request->libelle_ligne[$i] ?? null,
                        'tiers_id' => $request->client_id,
                    ];
                }
            }

            $numero = 'EC-' . now()->format('Ymd') . '-' . str_pad(EcritureComptable::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);

            $ecriture = EcritureComptable::create([
                'cabinet_id' => $user->cabinet_id,
                'client_id' => $request->client_id,
                'journal_id' => $request->journal_id,
                'numero' => $numero,
                'date_ecriture' => $request->date_ecriture,
                'date_piece' => $request->date_piece,
                'ref_piece' => $request->ref_piece,
                'libelle' => $request->libelle,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'createur_id' => $user->id,
                'notes' => $request->notes,
                'valide' => false,
            ]);

            foreach ($lignes as $ligne) {
                $ligne['ecriture_id'] = $ecriture->id;
                LigneEcriture::create($ligne);
            }

            DB::commit();

            return redirect()->route('gel-accountant.comptabilite.ecritures')
                ->with('success', 'Écriture créée avec succès. N° ' . $numero);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ecriture = EcritureComptable::where('cabinet_id', $user->cabinet_id)
            ->with(['lignes.compte', 'journal', 'client', 'createur', 'validePar'])
            ->findOrFail($id);

        return view('gel-accountant.comptabilite.ecritures.show', compact('ecriture'));
    }

    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ecriture = EcritureComptable::where('cabinet_id', $user->cabinet_id)
            ->with('lignes')
            ->findOrFail($id);

        if ($ecriture->valide) {
            return redirect()->route('gel-accountant.comptabilite.ecritures')
                ->with('error', 'Impossible de modifier une écriture validée.');
        }

        $clients = Client::where('cabinet_id', $user->cabinet_id)->actif()->get(['id', 'nom_entreprise']);
        $journaux = Journal::where('cabinet_id', $user->cabinet_id)->actif()->get(['id', 'code', 'libelle']);
        $comptes = CompteComptable::where('cabinet_id', $user->cabinet_id)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'intitule']);

        return view('gel-accountant.comptabilite.ecritures.edit', compact('ecriture', 'clients', 'journaux', 'comptes'));
    }

    public function valider($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ecriture = EcritureComptable::where('cabinet_id', $user->cabinet_id)->findOrFail($id);

        if (!$ecriture->estEquilibree()) {
            return back()->withErrors(['error' => 'L\'écriture n\'est pas équilibrée (Débit: ' .
                $ecriture->total_debit . ', Crédit: ' . $ecriture->total_credit . ')']);
        }

        $ecriture->update([
            'valide' => true,
            'valide_at' => now(),
            'valide_par' => $user->id,
        ]);

        return redirect()->route('gel-accountant.comptabilite.ecritures')
            ->with('success', 'Écriture validée avec succès.');
    }

    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ecriture = EcritureComptable::where('cabinet_id', $user->cabinet_id)->findOrFail($id);

        if ($ecriture->valide) {
            return back()->withErrors(['error' => 'Impossible de supprimer une écriture validée.']);
        }

        DB::transaction(function () use ($ecriture) {
            LigneEcriture::where('ecriture_id', $ecriture->id)->delete();
            $ecriture->delete();
        });

        return redirect()->route('gel-accountant.comptabilite.ecritures')
            ->with('success', 'Écriture supprimée.');
    }
}
