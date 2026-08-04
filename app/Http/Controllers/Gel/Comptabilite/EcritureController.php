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
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur de gestion des écritures comptables.
 * Opérations CRUD complètes avec validation de l'équilibre débit/crédit,
 * gestion des lignes d'écriture, validation, export PDF et CSV.
 * Respecte les règles SYSCOHADA : exercice comptable, journaux, etc.
 */
class EcritureController extends Controller
{
    /**
     * Liste paginée des écritures avec filtres multiples.
     * Filtres disponibles : journal, exercice, client, période, statut, recherche texte.
     * Données pour les filtres également retournées (journaux, exercices, clients).
     *
     * @param Request $request La requête HTTP avec les filtres optionnels
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;

        $query = EcritureComptable::byCabinet($cabinetId)
            ->with(['journal:id,code,libelle', 'exercice:id,libelle', 'client:id,company_name', 'createur:id,name', 'validateur:id,name']);

        // Filtre par journal
        if ($journalId = $request->input('journal_id')) {
            $query->where('journal_id', $journalId);
        }

        // Filtre par exercice
        if ($exerciceId = $request->input('exercice_id')) {
            $query->where('exercice_id', $exerciceId);
        }

        // Filtre par client
        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        // Filtre par période
        if ($dateDebut = $request->input('date_debut')) {
            $query->where('date_ecriture', '>=', $dateDebut);
        }
        if ($dateFin = $request->input('date_fin')) {
            $query->where('date_ecriture', '<=', $dateFin);
        }

        // Filtre par statut (validée ou non)
        if ($request->has('valide')) {
            $query->where('valide', $request->boolean('valide'));
        }

        // Recherche par libellé, numéro ou référence de pièce
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('libelle', 'like', "%{$search}%")
                  ->orWhere('reference_piece', 'like', "%{$search}%");
            });
        }

        $ecritures = $query->orderBy('date_ecriture', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 20))
            ->withQueryString();

        // Données pour les listes déroulantes des filtres
        $journaux = Journal::byCabinet($cabinetId)->actif()->get(['id', 'code', 'libelle']);
        $exercices = ExerciceComptable::byCabinet($cabinetId)->orderBy('date_debut', 'desc')->get(['id', 'libelle']);
        $clients = Client::whereHas('gelEcritures', function ($q) use ($cabinetId) {
            $q->where('cabinet_id', $cabinetId);
        })->get(['id', 'company_name']);

        if ($request->wantsJson()) {
            return response()->json([
                'ecritures' => $ecritures,
                'filters' => compact('journaux', 'exercices', 'clients'),
            ]);
        }

        return view('gel.comptabilite.ecritures.index', compact('ecritures', 'journaux', 'exercices', 'clients'));
    }

    /**
     * Affiche le formulaire de création d'une écriture.
     * Liste les journaux actifs, les exercices ouverts et les clients.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $cabinetId = Auth::user()->cabinet_id;

        $journaux = Journal::byCabinet($cabinetId)->actif()->get();
        $exercices = ExerciceComptable::byCabinet($cabinetId)->ouvert()->get();
        $clients = Client::whereHas('gelExercices', function ($q) use ($cabinetId) {
            $q->where('cabinet_id', $cabinetId);
        })->get(['id', 'company_name']);

        return view('gel.comptabilite.ecritures.create', compact('journaux', 'exercices', 'clients'));
    }

    /**
     * Enregistre une nouvelle écriture comptable.
     * Valide les données, vérifie l'équilibre débit/crédit, crée l'écriture
     * et ses lignes dans une transaction. Si l'utilisateur a les droits,
     * l'écriture est automatiquement validée.
     *
     * @param Request $request La requête HTTP avec les données de l'écriture et ses lignes
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;

        $validated = $request->validate([
            'journal_id' => ['required', 'exists:gel_journaux,id',
                function ($attr, $value, $fail) use ($cabinetId) {
                    $journal = Journal::where('id', $value)->where('cabinet_id', $cabinetId)->where('actif', true)->first();
                    if (!$journal) $fail('Journal invalide ou inactif.');
                },
            ],
            'exercice_id' => ['required', 'exists:gel_exercices,id',
                function ($attr, $value, $fail) use ($cabinetId) {
                    $exercice = ExerciceComptable::where('id', $value)->where('cabinet_id', $cabinetId)->first();
                    if (!$exercice) $fail('Exercice invalide.');
                    elseif ($exercice->estClos()) $fail('Cet exercice est clôturé.');
                },
            ],
            'client_id' => 'nullable|exists:clients,id',
            'date_ecriture' => 'required|date',
            'date_piece' => 'required|date|after_or_equal:date_ecriture',
            'reference_piece' => 'nullable|string|max:255',
            'libelle' => 'required|string|max:1000',
            'lignes' => 'required|array|min:2',
            'lignes.*.compte_id' => 'required|exists:gel_comptes_comptables,id',
            'lignes.*.sens' => 'required|in:debit,credit',
            'lignes.*.montant' => 'required|numeric|min:0.01',
            'lignes.*.tiers_id' => 'nullable|exists:clients,id',
            'lignes.*.libelle_ligne' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $journal = Journal::find($validated['journal_id']);

            // Calcul des totaux débit et crédit
            $totalDebit = 0;
            $totalCredit = 0;
            $lignesData = [];

            foreach ($validated['lignes'] as $ligne) {
                if ($ligne['sens'] === 'debit') {
                    $totalDebit += $ligne['montant'];
                } else {
                    $totalCredit += $ligne['montant'];
                }
                $lignesData[] = $ligne;
            }

            // Vérifier l'équilibre de l'écriture (tolérance de 0.01)
            if (abs($totalDebit - $totalCredit) > 0.01) {
                DB::rollBack();
                $error = "L'écriture n'est pas équilibrée (Débit: {$totalDebit}, Crédit: {$totalCredit}).";
                if ($request->wantsJson()) {
                    return response()->json(['message' => $error], 422);
                }
                return back()->withErrors(['lignes' => $error])->withInput();
            }

            $userId = Auth::id();
            $canValider = Auth::user()->can('comptabilite.valider');

            // Création de l'écriture avec validation automatique si droits suffisants
            $ecriture = EcritureComptable::create([
                'cabinet_id' => $cabinetId,
                'exercice_id' => $validated['exercice_id'],
                'journal_id' => $journal->id,
                'client_id' => $validated['client_id'] ?? null,
                'numero' => $journal->numero_suivant,
                'date_ecriture' => $validated['date_ecriture'],
                'date_piece' => $validated['date_piece'],
                'reference_piece' => $validated['reference_piece'] ?? null,
                'libelle' => $validated['libelle'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'valide' => $canValider,
                'valide_par' => $canValider ? $userId : null,
                'date_validation' => $canValider ? now() : null,
                'created_by' => $userId,
            ]);

            // Créer les lignes d'écriture
            foreach ($lignesData as $ligne) {
                $ecriture->lignes()->create([
                    'compte_id' => $ligne['compte_id'],
                    'sens' => $ligne['sens'],
                    'montant' => $ligne['montant'],
                    'tiers_id' => $ligne['tiers_id'] ?? null,
                    'libelle_ligne' => $ligne['libelle_ligne'] ?? null,
                ]);
            }

            DB::commit();

            $ecriture->load(['lignes.compte', 'journal', 'exercice']);

            $message = $canValider
                ? "Écriture {$ecriture->numero} créée et validée."
                : "Écriture {$ecriture->numero} créée en brouillard.";

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'ecriture' => $ecriture,
                ], 201);
            }

            return redirect()->route('gel.comptabilite.ecritures.show', $ecriture->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Erreur : ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Affiche le détail d'une écriture avec toutes ses lignes et relations.
     *
     * @param int $id L'identifiant de l'écriture
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $ecriture = EcritureComptable::byCabinet($cabinetId)
            ->with([
                'lignes' => function ($q) {
                    $q->orderBy('sens')->orderBy('id');
                },
                'lignes.compte:id,code,intitule',
                'lignes.tiers:id,company_name',
                'journal:id,code,libelle',
                'exercice:id,libelle,date_debut,date_fin',
                'client:id,company_name',
                'createur:id,name',
                'validateur:id,name',
            ])
            ->findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json(['ecriture' => $ecriture]);
        }

        return view('gel.comptabilite.ecritures.show', compact('ecriture'));
    }

    /**
     * Affiche le formulaire d'édition (uniquement si l'écriture n'est pas validée).
     *
     * @param int $id L'identifiant de l'écriture
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $ecriture = EcritureComptable::byCabinet($cabinetId)
            ->with('lignes')
            ->findOrFail($id);

        if ($ecriture->estValidee()) {
            return back()->withErrors(['message' => 'Impossible de modifier une écriture validée.']);
        }

        $journaux = Journal::byCabinet($cabinetId)->actif()->get();
        $exercices = ExerciceComptable::byCabinet($cabinetId)->ouvert()->get();
        $clients = Client::whereHas('gelExercices', fn($q) => $q->where('cabinet_id', $cabinetId))
            ->get(['id', 'company_name']);

        return view('gel.comptabilite.ecritures.edit', compact('ecriture', 'journaux', 'exercices', 'clients'));
    }

    /**
     * Met à jour une écriture (uniquement si non validée).
     * Remplace les anciennes lignes par les nouvelles après vérification de l'équilibre.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant de l'écriture
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $ecriture = EcritureComptable::byCabinet($cabinetId)->findOrFail($id);

        if ($ecriture->estValidee()) {
            return response()->json(['message' => 'Impossible de modifier une écriture validée.'], 403);
        }

        $validated = $request->validate([
            'journal_id' => 'required|exists:gel_journaux,id',
            'exercice_id' => 'required|exists:gel_exercices,id',
            'client_id' => 'nullable|exists:clients,id',
            'date_ecriture' => 'required|date',
            'date_piece' => 'required|date',
            'reference_piece' => 'nullable|string|max:255',
            'libelle' => 'required|string|max:1000',
            'lignes' => 'required|array|min:2',
            'lignes.*.compte_id' => 'required|exists:gel_comptes_comptables,id',
            'lignes.*.sens' => 'required|in:debit,credit',
            'lignes.*.montant' => 'required|numeric|min:0.01',
            'lignes.*.tiers_id' => 'nullable|exists:clients,id',
            'lignes.*.libelle_ligne' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $totalDebit = collect($validated['lignes'])->where('sens', 'debit')->sum('montant');
            $totalCredit = collect($validated['lignes'])->where('sens', 'credit')->sum('montant');

            // Vérifier l'équilibre avant mise à jour
            if (abs($totalDebit - $totalCredit) > 0.01) {
                DB::rollBack();
                return response()->json(['message' => "L'écriture n'est pas équilibrée."], 422);
            }

            $ecriture->update([
                'journal_id' => $validated['journal_id'],
                'exercice_id' => $validated['exercice_id'],
                'client_id' => $validated['client_id'] ?? null,
                'date_ecriture' => $validated['date_ecriture'],
                'date_piece' => $validated['date_piece'],
                'reference_piece' => $validated['reference_piece'] ?? null,
                'libelle' => $validated['libelle'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Supprimer les anciennes lignes et recréer avec les nouvelles données
            $ecriture->lignes()->delete();
            foreach ($validated['lignes'] as $ligne) {
                $ecriture->lignes()->create([
                    'compte_id' => $ligne['compte_id'],
                    'sens' => $ligne['sens'],
                    'montant' => $ligne['montant'],
                    'tiers_id' => $ligne['tiers_id'] ?? null,
                    'libelle_ligne' => $ligne['libelle_ligne'] ?? null,
                ]);
            }

            DB::commit();
            $ecriture->load(['lignes.compte', 'journal', 'exercice']);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Écriture mise à jour.', 'ecriture' => $ecriture]);
            }

            return redirect()->route('gel.comptabilite.ecritures.show', $ecriture->id)
                ->with('success', 'Écriture mise à jour.');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Supprime une écriture (uniquement si non validée).
     * Supprime d'abord les lignes associées, puis l'écriture elle-même.
     *
     * @param int $id L'identifiant de l'écriture
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $ecriture = EcritureComptable::byCabinet($cabinetId)->findOrFail($id);

        if ($ecriture->estValidee()) {
            return response()->json(['message' => 'Impossible de supprimer une écriture validée.'], 403);
        }

        $ecriture->lignes()->delete();
        $ecriture->delete();

        return response()->json(['message' => 'Écriture supprimée.']);
    }

    /**
     * Validation d'une écriture par un comptable senior.
     * Délègue la validation au modèle EcritureComptable.
     *
     * @param int $id L'identifiant de l'écriture à valider
     * @return \Illuminate\Http\JsonResponse
     */
    public function valider($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $ecriture = EcritureComptable::byCabinet($cabinetId)->findOrFail($id);

        try {
            $ecriture->valider(Auth::id());
            return response()->json(['message' => 'Écriture validée avec succès.', 'ecriture' => $ecriture->fresh()->load('validateur')]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Génération et téléchargement PDF d'une écriture.
     *
     * @param int $id L'identifiant de l'écriture
     * @return \Illuminate\Http\Response
     */
    public function pdf($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $ecriture = EcritureComptable::byCabinet($cabinetId)
            ->with(['lignes.compte', 'journal', 'exercice', 'validateur', 'createur'])
            ->findOrFail($id);

        try {
            $pdf = $ecriture->pdf();
            return $pdf->download("ecriture-{$ecriture->numero}.pdf");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur génération PDF : ' . $e->getMessage()]);
        }
    }

    /**
     * Export CSV des écritures filtrées.
     * Génère un fichier CSV avec BOM UTF-8 contenant les colonnes :
     * Numéro, Date, Journal, Client, Libellé, Débit, Crédit, Validée, Date validation.
     *
     * @param Request $request La requête HTTP avec les filtres (date_debut, date_fin, journal_id)
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;

        $query = EcritureComptable::byCabinet($cabinetId)
            ->with(['journal:id,code,libelle', 'client:id,company_name']);

        if ($request->filled('date_debut')) $query->where('date_ecriture', '>=', $request->date_debut);
        if ($request->filled('date_fin')) $query->where('date_ecriture', '<=', $request->date_fin);
        if ($request->filled('journal_id')) $query->where('journal_id', $request->journal_id);

        $ecritures = $query->orderBy('date_ecriture')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="ecritures.csv"',
        ];

        $callback = function () use ($ecritures) {
            $output = fopen('php://output', 'w');
            // BOM UTF-8 pour la compatibilité Excel
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, ['Numéro', 'Date', 'Journal', 'Client', 'Libellé', 'Débit', 'Crédit', 'Validée', 'Date validation']);

            foreach ($ecritures as $e) {
                fputcsv($output, [
                    $e->numero,
                    $e->date_ecriture->format('d/m/Y'),
                    $e->journal?->code,
                    $e->client?->company_name,
                    $e->libelle,
                    number_format((float) $e->total_debit, 2, ',', ''),
                    number_format((float) $e->total_credit, 2, ',', ''),
                    $e->valide ? 'Oui' : 'Non',
                    $e->date_validation?->format('d/m/Y H:i'),
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
