<?php

namespace App\Http\Controllers\Gel\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Gel\Comptabilite\CompteComptable;
use App\Models\Gel\Comptabilite\EcritureComptable;
use App\Models\Gel\Comptabilite\ExerciceComptable;
use App\Models\Gel\Comptabilite\Journal;
use App\Models\Gel\Comptabilite\LigneEcriture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrandLivreController extends Controller
{
    /**
     * Formulaire du Grand Livre avec filtres.
     */
    public function index(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;

        $comptes = CompteComptable::byCabinet($cabinetId)->actif()->orderBy('code')->get();
        $journaux = Journal::byCabinet($cabinetId)->actif()->get();
        $exercices = ExerciceComptable::byCabinet($cabinetId)->orderBy('date_debut', 'desc')->get();
        $clients = Client::whereHas('gelEcritures', fn($q) => $q->where('cabinet_id', $cabinetId))->get(['id', 'company_name']);

        // Paramètres
        $compteId = $request->input('compte_id');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $clientId = $request->input('client_id');
        $journalId = $request->input('journal_id');
        $exerciceId = $request->input('exercice_id');

        $lignes = collect();
        $compte = null;
        $soldeInitial = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        $soldeFinal = 0;

        if ($compteId) {
            $compte = CompteComptable::byCabinet($cabinetId)->findOrFail($compteId);

            $query = LigneEcriture::where('compte_id', $compteId)
                ->whereHas('ecriture', function ($q) use ($cabinetId, $dateDebut, $dateFin, $clientId, $journalId, $exerciceId) {
                    $q->byCabinet($cabinetId);

                    if ($dateDebut) $q->where('date_ecriture', '>=', $dateDebut);
                    if ($dateFin) $q->where('date_ecriture', '<=', $dateFin);
                    if ($clientId) $q->where('client_id', $clientId);
                    if ($journalId) $q->where('journal_id', $journalId);
                    if ($exerciceId) $q->where('exercice_id', $exerciceId);
                })
                ->with([
                    'ecriture' => function ($q) {
                        $q->with('journal:id,code,libelle')
                          ->select('id', 'numero', 'date_ecriture', 'libelle', 'journal_id');
                    },
                ])
                ->orderBy('id');

            $lignes = $query->get();
            $totalDebit = $lignes->where('sens', 'debit')->sum('montant');
            $totalCredit = $lignes->where('sens', 'credit')->sum('montant');

            // Solde initial = total débit - total crédit
            $soldeInitial = 0;
            if ($dateDebut) {
                $soldeAvant = LigneEcriture::where('compte_id', $compteId)
                    ->whereHas('ecriture', function ($q) use ($cabinetId, $dateDebut, $clientId) {
                        $q->byCabinet($cabinetId)->where('date_ecriture', '<', $dateDebut);
                        if ($clientId) $q->where('client_id', $clientId);
                    })
                    ->selectRaw('SUM(CASE WHEN sens = "debit" THEN montant ELSE 0 END) as debit, SUM(CASE WHEN sens = "credit" THEN montant ELSE 0 END) as credit')
                    ->first();
                $soldeInitial = ($soldeAvant->debit ?? 0) - ($soldeAvant->credit ?? 0);
            }

            $soldeFinal = $soldeInitial + ($totalDebit - $totalCredit);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'compte' => $compte,
                'lignes' => $lignes,
                'solde_initial' => $soldeInitial,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'solde_final' => $soldeFinal,
            ]);
        }

        return view('gel.comptabilite.grand-livre.index', compact(
            'comptes', 'journaux', 'exercices', 'clients',
            'compte', 'lignes', 'soldeInitial', 'totalDebit', 'totalCredit', 'soldeFinal'
        ));
    }

    /**
     * Export CSV du Grand Livre.
     */
    public function export(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $compteId = $request->input('compte_id');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $clientId = $request->input('client_id');

        if (!$compteId) {
            return response()->json(['message' => 'Veuillez sélectionner un compte.'], 422);
        }

        $compte = CompteComptable::byCabinet($cabinetId)->findOrFail($compteId);

        $lignes = LigneEcriture::where('compte_id', $compteId)
            ->whereHas('ecriture', function ($q) use ($cabinetId, $dateDebut, $dateFin, $clientId) {
                $q->byCabinet($cabinetId)->where('valide', true);
                if ($dateDebut) $q->where('date_ecriture', '>=', $dateDebut);
                if ($dateFin) $q->where('date_ecriture', '<=', $dateFin);
                if ($clientId) $q->where('client_id', $clientId);
            })
            ->with('ecriture:id,numero,date_ecriture,libelle,journal_id')
            ->orderBy('id')
            ->get();

        $callback = function () use ($compte, $lignes) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['GRAND LIVRE — ' . $compte->code . ' — ' . $compte->intitule]);
            fputcsv($output, ['Date', 'Numéro', 'Libellé', 'Débit', 'Crédit', 'Solde']);

            $solde = 0;
            foreach ($lignes as $ligne) {
                $montant = (float) $ligne->montant;
                if ($ligne->sens === 'debit') {
                    $solde += $montant;
                    fputcsv($output, [
                        $ligne->ecriture->date_ecriture->format('d/m/Y'),
                        $ligne->ecriture->numero,
                        $ligne->libelle_ligne ?? $ligne->ecriture->libelle,
                        number_format($montant, 2, ',', ''),
                        '',
                        number_format($solde, 2, ',', ''),
                    ]);
                } else {
                    $solde -= $montant;
                    fputcsv($output, [
                        $ligne->ecriture->date_ecriture->format('d/m/Y'),
                        $ligne->ecriture->numero,
                        $ligne->libelle_ligne ?? $ligne->ecriture->libelle,
                        '',
                        number_format($montant, 2, ',', ''),
                        number_format($solde, 2, ',', ''),
                    ]);
                }
            }

            fclose($output);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="grand-livre-{$compte->code}.csv"',
        ]);
    }

    /**
     * Balance générale.
     */
    public function balance(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $clientId = $request->input('client_id');
        $exerciceId = $request->input('exercice_id');

        $comptes = CompteComptable::byCabinet($cabinetId)->actif()->orderBy('code')->get();
        $resultats = [];

        foreach ($comptes as $compte) {
            $query = LigneEcriture::where('compte_id', $compte->id)
                ->whereHas('ecriture', function ($q) use ($cabinetId, $dateDebut, $dateFin, $clientId, $exerciceId) {
                    $q->byCabinet($cabinetId)->where('valide', true);
                    if ($dateDebut) $q->where('date_ecriture', '>=', $dateDebut);
                    if ($dateFin) $q->where('date_ecriture', '<=', $dateFin);
                    if ($clientId) $q->where('client_id', $clientId);
                    if ($exerciceId) $q->where('exercice_id', $exerciceId);
                });

            $totalDebit = (float) (clone $query)->where('sens', 'debit')->sum('montant');
            $totalCredit = (float) (clone $query)->where('sens', 'credit')->sum('montant');
            $solde = $totalDebit - $totalCredit;

            $resultats[] = [
                'code' => $compte->code,
                'intitule' => $compte->intitule,
                'classe' => $compte->classe,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'solde_debiteur' => $solde > 0 ? $solde : 0,
                'solde_crediteur' => $solde < 0 ? abs($solde) : 0,
            ];
        }

        $totalGeneralDebit = array_sum(array_column($resultats, 'total_debit'));
        $totalGeneralCredit = array_sum(array_column($resultats, 'total_credit'));

        if ($request->wantsJson()) {
            return response()->json([
                'lignes' => $resultats,
                'total_debit' => $totalGeneralDebit,
                'total_credit' => $totalGeneralCredit,
            ]);
        }

        $exercices = \App\Models\Gel\Comptabilite\ExerciceComptable::byCabinet($cabinetId)->orderBy('date_debut', 'desc')->get();
        $balances = $resultats;

        return view('gel.comptabilite.balance.index', compact('balances', 'totalGeneralDebit', 'totalGeneralCredit', 'exercices'));
    }

    /**
     * États financiers : Bilan, Résultat, SIG.
     */
    public function etatsFinanciers(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $exerciceId = $request->input('exercice_id');
        $clientId = $request->input('client_id');

        if (!$exerciceId) {
            $exercice = ExerciceComptable::byCabinet($cabinetId)->encours()->first();
            $exerciceId = $exercice?->id;
        }

        // Récupérer les soldes par compte
        $soldes = LigneEcriture::select(
                'compte_id',
                DB::raw('SUM(CASE WHEN sens = "debit" THEN montant ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN sens = "credit" THEN montant ELSE 0 END) as total_credit')
            )
            ->whereHas('ecriture', function ($q) use ($cabinetId, $exerciceId, $clientId) {
                $q->byCabinet($cabinetId)->where('valide', true);
                if ($exerciceId) $q->where('exercice_id', $exerciceId);
                if ($clientId) $q->where('client_id', $clientId);
            })
            ->groupBy('compte_id')
            ->get()
            ->keyBy('compte_id');

        $comptes = CompteComptable::byCabinet($cabinetId)->get()->keyBy('id');

        // Bilan
        $bilan = [
            'actif' => ['sections' => [], 'total' => 0],
            'passif' => ['sections' => [], 'total' => 0],
        ];

        $classesActif = ['2', '3', '5']; // Immobilisations, Stocks, Trésorerie
        $classesPassif = ['1', '4']; // Capitaux, Tiers (fournisseurs, dettes)

        foreach ($classesActif as $classe) {
            $comptesClasse = $comptes->where('classe', $classe);
            $totalClasse = 0;
            $lignesBilan = [];

            foreach ($comptesClasse as $c) {
                $solde = $soldes->get($c->id);
                if (!$solde) continue;
                $montant = (float) $solde->total_debit - (float) $solde->total_credit;
                if (abs($montant) < 0.01) continue;
                $lignesBilan[] = [
                    'code' => $c->code,
                    'intitule' => $c->intitule,
                    'montant' => abs($montant),
                ];
                $totalClasse += abs($montant);
            }

            $bilan['actif']['sections'][] = [
                'titre' => "Classe {$classe}",
                'lignes' => $lignesBilan,
                'total' => $totalClasse,
            ];
            $bilan['actif']['total'] += $totalClasse;
        }

        foreach ($classesPassif as $classe) {
            $comptesClasse = $comptes->where('classe', $classe);
            $totalClasse = 0;
            $lignesBilan = [];

            foreach ($comptesClasse as $c) {
                $solde = $soldes->get($c->id);
                if (!$solde) continue;
                $montant = (float) $solde->total_credit - (float) $solde->total_debit;
                if (abs($montant) < 0.01) continue;
                $lignesBilan[] = [
                    'code' => $c->code,
                    'intitule' => $c->intitule,
                    'montant' => abs($montant),
                ];
                $totalClasse += abs($montant);
            }

            $bilan['passif']['sections'][] = [
                'titre' => "Classe {$classe}",
                'lignes' => $lignesBilan,
                'total' => $totalClasse,
            ];
            $bilan['passif']['total'] += $totalClasse;
        }

        // Compte de résultat
        $charges = CompteComptable::byCabinet($cabinetId)->where('classe', '6')->get();
        $produits = CompteComptable::byCabinet($cabinetId)->where('classe', '7')->get();

        $resultat = [
            'charges' => ['lignes' => [], 'total' => 0],
            'produits' => ['lignes' => [], 'total' => 0],
        ];

        foreach ($charges as $c) {
            $solde = $soldes->get($c->id);
            if (!$solde) continue;
            $montant = (float) $solde->total_debit - (float) $solde->total_credit;
            if (abs($montant) < 0.01) continue;
            $resultat['charges']['lignes'][] = [
                'code' => $c->code, 'intitule' => $c->intitule, 'montant' => abs($montant),
            ];
            $resultat['charges']['total'] += abs($montant);
        }

        foreach ($produits as $c) {
            $solde = $soldes->get($c->id);
            if (!$solde) continue;
            $montant = (float) $solde->total_credit - (float) $solde->total_debit;
            if (abs($montant) < 0.01) continue;
            $resultat['produits']['lignes'][] = [
                'code' => $c->code, 'intitule' => $c->intitule, 'montant' => abs($montant),
            ];
            $resultat['produits']['total'] += abs($montant);
        }

        $resultatNet = $resultat['produits']['total'] - $resultat['charges']['total'];

        if ($request->wantsJson()) {
            return response()->json([
                'bilan' => $bilan,
                'resultat' => $resultat,
                'resultat_net' => $resultatNet,
                'exercice' => ExerciceComptable::find($exerciceId),
            ]);
        }

        $exercices = ExerciceComptable::byCabinet($cabinetId)->orderBy('date_debut', 'desc')->get();

        return view('gel.comptabilite.etats-financiers.index', compact('bilan', 'resultat', 'resultatNet', 'exercices'));
    }
}
