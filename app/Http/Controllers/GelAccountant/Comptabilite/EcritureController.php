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

/**
 * Contrôleur de gestion des écritures comptables.
 *
 * Permet de lister, créer, visualiser, modifier, valider et supprimer
 * les écritures comptables d'un cabinet. Chaque écriture est composée
 * de lignes au débit et au crédit, et son équilibre est vérifié avant
 * validation. Les écritures validées ne peuvent plus être modifiées
 * ni supprimées.
 */
class EcritureController extends Controller
{
    /**
     * Affiche la liste paginée des écritures comptables.
     *
     * Les écritures peuvent être filtrées par client, journal, intervalle
     * de dates et statut (validée / en attente). Les totaux débit et crédit
     * de la page courante sont transmis à la vue.
     *
     * @param  Request $request La requête avec les filtres optionnels
     *                          (client_id, journal_id, date_from, date_to,
     *                          statut).
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = EcritureComptable::where('cabinet_id', $cabinetId)
            ->with(['journal:id,code', 'client:id,nom_entreprise', 'lignes']);

        // Filtre par client
        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }
        // Filtre par journal comptable
        if ($journalId = $request->input('journal_id')) {
            $query->where('journal_id', $journalId);
        }
        // Filtre par date de début
        if ($dateFrom = $request->input('date_from')) {
            $query->where('date_ecriture', '>=', $dateFrom);
        }
        // Filtre par date de fin
        if ($dateTo = $request->input('date_to')) {
            $query->where('date_ecriture', '<=', $dateTo);
        }
        // Filtre par statut (valide / non valide)
        if ($statut = $request->input('statut')) {
            $query->where('valide', $statut === 'valide');
        }

        $ecritures = $query->orderBy('date_ecriture', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        // Totaux cumulés de la page courante (affichés dans le pied du tableau)
        $totalDebit = $ecritures->sum('total_debit');
        $totalCredit = $ecritures->sum('total_credit');

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);
        $journaux = Journal::where('cabinet_id', $cabinetId)->actif()->get(['id', 'code', 'libelle']);

        return view('gel-accountant.comptabilite.ecritures.index', compact(
            'ecritures', 'totalDebit', 'totalCredit', 'clients', 'journaux'
        ) + ['currentSection' => 'comptabilite', 'currentPage' => 'ecritures']);
    }

    /**
     * Affiche le formulaire de création d'une écriture comptable.
     *
     * Fournit les listes des clients actifs, journaux et comptes de
     * niveau supérieur à zéro nécessaires à la saisie d'une nouvelle
     * écriture.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);
        $journaux = Journal::where('cabinet_id', $cabinetId)->actif()->get(['id', 'code', 'libelle']);
        // Seuls les comptes de niveau > 0 (sous-comptes, pas les classes)
        // sont proposés pour le lettrage des lignes d'écriture
        $comptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'intitule']);

        return view('gel-accountant.comptabilite.ecritures.create', compact('clients', 'journaux', 'comptes') + ['currentSection' => 'comptabilite', 'currentPage' => 'ecritures']);
    }

    /**
     * Enregistre une nouvelle écriture comptable avec ses lignes.
     *
     * Valide les données entrantes (avec validation des tableaux de
     * comptes, débits et crédits), calcule le numéro d'écriture
     * séquentiel du jour, crée l'écriture et ses lignes dans une
     * transaction. En cas d'erreur, la transaction est annulée.
     *
     * @param  Request $request La requête contenant l'écriture et
     *                          ses lignes (compte_id, debit, credit).
     * @return \Illuminate\Http\RedirectResponse
     */
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

        // L'ensemble de la création se fait dans une transaction
        // pour garantir l'intégrité écriture + lignes
        DB::beginTransaction();
        try {
            $totalDebit = 0;
            $totalCredit = 0;
            $lignes = [];

            // Parcours des lignes soumises par le formulaire
            foreach ($request->compte_id as $i => $compteId) {
                $debit = (int)($request->debit[$i] ?? 0);
                $credit = (int)($request->credit[$i] ?? 0);
                $totalDebit += $debit;
                $totalCredit += $credit;

                // On ne crée une ligne que si le montant est non nul
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

            // Génération du numéro d'écriture séquentiel par jour
            // Format : EC-AAAAMMJJ-NNN
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

            // Log géré par EcritureObserver (created)

            DB::commit();

            return redirect()->route('gel-accountant.comptabilite.ecritures')
                ->with('success', 'Écriture créée avec succès. N° ' . $numero);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Affiche le détail d'une écriture comptable.
     *
     * Charge l'écriture avec ses lignes, le compte associé à chaque
     * ligne, le journal, le client, le créateur et le valideur.
     *
     * @param  int $id L'identifiant de l'écriture à afficher.
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ecriture = EcritureComptable::where('cabinet_id', $user->cabinet_id)
            ->with(['lignes.compte', 'journal', 'client', 'createur', 'validePar'])
            ->findOrFail($id);

        \App\Services\AuditService::log('consulted', $ecriture);

        // Fetch logs for this model to display in a timeline
        $logs = \App\Models\Gel\AuditLog::where('cabinet_id', $user->cabinet_id)
            ->where('auditable_type', get_class($ecriture))
            ->where('auditable_id', $ecriture->id)
            ->latest()
            ->get();

        return view('gel-accountant.comptabilite.ecritures.show', compact('ecriture', 'logs') + ['currentSection' => 'comptabilite', 'currentPage' => 'ecritures']);
    }

    /**
     * Affiche le formulaire de modification d'une écriture.
     *
     * Vérifie au préalable que l'écriture n'est pas déjà validée,
     * car une écriture validée est verrouillée.
     *
     * @param  int $id L'identifiant de l'écriture à modifier.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ecriture = EcritureComptable::where('cabinet_id', $user->cabinet_id)
            ->with('lignes')
            ->findOrFail($id);

        // Blocage : on ne peut pas modifier une écriture déjà validée
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

        return view('gel-accountant.comptabilite.ecritures.edit', compact('ecriture', 'clients', 'journaux', 'comptes') + ['currentSection' => 'comptabilite', 'currentPage' => 'ecritures']);
    }

    /**
     * Valide une écriture comptable (la rend définitive).
     *
     * Vérifie d'abord que l'écriture est équilibrée (total débit =
     * total crédit). Enregistre l'utilisateur et la date de validation.
     * Une fois validée, l'écriture ne peut plus être modifiée ou
     * supprimée.
     *
     * @param  int $id L'identifiant de l'écriture à valider.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function valider($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ecriture = EcritureComptable::where('cabinet_id', $user->cabinet_id)->findOrFail($id);

        // Refuser la validation si l'écriture n'est pas équilibrée
        if (!$ecriture->estEquilibree()) {
            return back()->withErrors(['error' => 'L\'écriture n\'est pas équilibrée (Débit: ' .
                $ecriture->total_debit . ', Crédit: ' . $ecriture->total_credit . ')']);
        }

        $before = $ecriture->toArray();

        $ecriture->update([
            'valide' => true,
            'valide_at' => now(),
            'valide_par' => $user->id,
        ]);

        \App\Services\AuditService::log('validated', $ecriture, $before, $ecriture->toArray());

        return redirect()->route('gel-accountant.comptabilite.ecritures')
            ->with('success', 'Écriture validée avec succès.');
    }

    /**
     * Supprime une écriture comptable (non validée).
     *
     * Les lignes d'écriture sont supprimées en premier, puis l'écriture
     * elle-même, dans une même transaction.
     *
     * @param  int $id L'identifiant de l'écriture à supprimer.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ecriture = EcritureComptable::where('cabinet_id', $user->cabinet_id)->findOrFail($id);

        // L'écriture ne doit pas être déjà validée
        if ($ecriture->valide) {
            return back()->withErrors(['error' => 'Impossible de supprimer une écriture validée.']);
        }

        DB::beginTransaction();

        try {
            $before = $ecriture->toArray();

            // Suppression des lignes d'écriture (SoftDeletes s'applique si activé)
            $ecriture->lignes()->delete();

            // Suppression de l'écriture (SoftDeletes s'applique)
            $ecriture->delete();

            // Log géré par EcritureObserver (deleted)

            DB::commit();

            return redirect()->route('gel-accountant.comptabilite.ecritures')
                ->with('success', 'Écriture supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
        }
    }
}
