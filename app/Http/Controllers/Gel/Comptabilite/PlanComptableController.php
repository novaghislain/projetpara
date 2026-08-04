<?php

namespace App\Http\Controllers\Gel\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\Comptabilite\CompteComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du plan comptable SYSCOHADA.
 * Gère les comptes comptables (classes 1 à 8), leur hiérarchie,
 * et les opérations de recherche, filtrage et désactivation.
 */
class PlanComptableController extends Controller
{
    /**
     * Liste paginée des comptes avec recherche et filtre par classe.
     * Supporte la recherche par code ou intitulé et le filtrage par classe.
     */
    public function index(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $query = CompteComptable::byCabinet($cabinetId)
            ->withCount('lignesEcriture')
            ->with('parent:id,code,intitule');

        // Recherche par code ou intitulé
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('intitule', 'like', "%{$search}%");
            });
        }

        // Filtre par classe
        if ($classe = $request->input('classe')) {
            $query->where('classe', $classe);
        }

        // Filtre actif/inactif
        if ($request->has('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        $comptes = $query->orderBy('code')->paginate(50);

        if ($request->wantsJson()) {
            return response()->json($comptes);
        }

        return view('gel.comptabilite.plan-comptable.index', compact('comptes'));
    }

    /**
     * Affiche le formulaire de création d'un compte comptable.
     * Liste les comptes parents disponibles pour établir la hiérarchie.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $cabinetId = Auth::user()->cabinet_id;
        $comptesParents = CompteComptable::byCabinet($cabinetId)
            ->actif()
            ->orderBy('code')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'label' => "{$c->code} — {$c->intitule}"]);

        return view('gel.comptabilite.plan-comptable.create', compact('comptesParents'));
    }

    /**
     * Enregistre un nouveau compte comptable.
     * Vérifie l'unicité du code et définit le solde débiteur par défaut selon la classe.
     *
     * @param Request $request La requête HTTP avec les données du compte
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10',
                function ($attr, $value, $fail) use ($cabinetId) {
                    $exists = CompteComptable::where('cabinet_id', $cabinetId)
                        ->where('code', $value)->exists();
                    if ($exists) $fail('Ce code existe déjà pour ce cabinet.');
                },
            ],
            'intitule' => 'required|string|max:255',
            'classe' => 'required|string|in:1,2,3,4,5,6,7,8',
            'niveau' => 'nullable|integer|min:1|max:5',
            'compte_parent_id' => 'nullable|exists:gel_comptes_comptables,id',
            'solde_debiteur' => 'nullable|boolean',
            'actif' => 'nullable|boolean',
        ]);

        $compte = CompteComptable::create([
            'cabinet_id' => $cabinetId,
            'code' => $validated['code'],
            'intitule' => $validated['intitule'],
            'classe' => $validated['classe'],
            'niveau' => $validated['niveau'] ?? 1,
            'compte_parent_id' => $validated['compte_parent_id'] ?? null,
            'solde_debiteur' => $validated['solde_debiteur'] ?? $this->defaultSoldeDebiteur($validated['classe']),
            'actif' => $validated['actif'] ?? true,
        ]);

        if ($request->wantsJson()) {
            return response()->json($compte, 201);
        }

        return redirect()->route('gel.comptabilite.plan-comptable.index')
            ->with('success', "Compte {$compte->code} — {$compte->intitule} créé avec succès.");
    }

    /**
     * Affiche le détail d'un compte avec son solde et ses relations.
     *
     * @param int $id L'identifiant du compte
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $compte = CompteComptable::byCabinet($cabinetId)
            ->with(['parent', 'enfants', 'lignesEcriture.ecriture'])
            ->withCount('enfants')
            ->findOrFail($id);

        // Calcul du solde du compte (débit - crédit)
        $totalDebit = $compte->lignesEcriture->where('sens', 'debit')->sum('montant');
        $totalCredit = $compte->lignesEcriture->where('sens', 'credit')->sum('montant');
        $solde = $totalDebit - $totalCredit;

        if (request()->wantsJson()) {
            return response()->json([
                'compte' => $compte,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'solde' => $solde,
            ]);
        }

        return view('gel.comptabilite.plan-comptable.show', compact('compte', 'totalDebit', 'totalCredit', 'solde'));
    }

    /**
     * Affiche le formulaire d'édition d'un compte comptable.
     * Exclut le compte lui-même de la liste des parents possibles.
     *
     * @param int $id L'identifiant du compte
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $compte = CompteComptable::byCabinet($cabinetId)->findOrFail($id);

        $comptesParents = CompteComptable::byCabinet($cabinetId)
            ->actif()
            ->where('id', '!=', $id)
            ->orderBy('code')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'label' => "{$c->code} — {$c->intitule}"]);

        return view('gel.comptabilite.plan-comptable.edit', compact('compte', 'comptesParents'));
    }

    /**
     * Met à jour un compte comptable.
     * Vérifie l'unicité du code en excluant le compte modifié.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant du compte
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $compte = CompteComptable::byCabinet($cabinetId)->findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10',
                function ($attr, $value, $fail) use ($cabinetId, $compte) {
                    $exists = CompteComptable::where('cabinet_id', $cabinetId)
                        ->where('code', $value)
                        ->where('id', '!=', $compte->id)
                        ->exists();
                    if ($exists) $fail('Ce code existe déjà pour ce cabinet.');
                },
            ],
            'intitule' => 'required|string|max:255',
            'classe' => 'required|string|in:1,2,3,4,5,6,7,8',
            'niveau' => 'nullable|integer|min:1|max:5',
            'compte_parent_id' => 'nullable|exists:gel_comptes_comptables,id',
            'solde_debiteur' => 'nullable|boolean',
            'actif' => 'nullable|boolean',
        ]);

        $compte->update($validated);

        if ($request->wantsJson()) {
            return response()->json($compte);
        }

        return redirect()->route('gel.comptabilite.plan-comptable.index')
            ->with('success', "Compte {$compte->code} mis à jour.");
    }

    /**
     * Désactive un compte comptable (pas de suppression physique).
     * Si le compte a des écritures, la désactivation est forcée plutôt que la suppression.
     *
     * @param int $id L'identifiant du compte
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $compte = CompteComptable::byCabinet($cabinetId)->findOrFail($id);

        // Vérifier que le compte n'a pas d'écritures avant suppression
        if ($compte->lignesEcriture()->exists()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'message' => 'Ce compte contient des écritures. Désactivez-le plutôt.',
                ], 409);
            }
            return back()->withErrors(['message' => 'Ce compte contient des écritures. Désactivez-le plutôt.']);
        }

        // Désactiver au lieu de supprimer physiquement
        $compte->update(['actif' => false]);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Compte désactivé.']);
        }

        return redirect()->route('gel.comptabilite.plan-comptable.index')
            ->with('success', "Compte {$compte->code} désactivé.");
    }

    /**
     * Retourne les comptes d'une classe donnée (pour les sélecteurs).
     *
     * @param Request $request La requête HTTP
     * @param string $classe Le numéro de classe (1 à 8)
     * @return \Illuminate\Http\JsonResponse
     */
    public function byClasse(Request $request, string $classe)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $comptes = CompteComptable::byCabinet($cabinetId)
            ->byClasse($classe)
            ->actif()
            ->orderBy('code')
            ->get(['id', 'code', 'intitule', 'solde_debiteur']);

        return response()->json($comptes);
    }

    /**
     * Arbre hiérarchique des comptes pour le composant Select2.
     * Organise les comptes par classe (1 à 8).
     *
     * @param Request $request La requête HTTP
     * @return \Illuminate\Http\JsonResponse
     */
    public function tree(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $classes = ['1', '2', '3', '4', '5', '6', '7', '8'];
        $tree = [];

        foreach ($classes as $classe) {
            $comptes = CompteComptable::byCabinet($cabinetId)
                ->byClasse($classe)
                ->actif()
                ->orderBy('code')
                ->get(['id', 'code', 'intitule', 'niveau']);

            $tree[] = [
                'label' => "Classe {$classe}",
                'children' => $comptes->map(fn($c) => [
                    'id' => $c->id,
                    'text' => "{$c->code} — {$c->intitule}",
                    'level' => $c->niveau,
                ]),
            ];
        }

        return response()->json($tree);
    }

    /**
     * Recherche rapide de comptes pour l'autocomplétion.
     * Limite à 20 résultats, filtrés par code ou intitulé.
     *
     * @param Request $request La requête HTTP avec le paramètre 'q'
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $query = $request->input('q', '');

        $comptes = CompteComptable::byCabinet($cabinetId)
            ->actif()
            ->where(function ($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                  ->orWhere('intitule', 'like', "%{$query}%");
            })
            ->orderBy('code')
            ->limit(20)
            ->get(['id', 'code', 'intitule', 'solde_debiteur']);

        return response()->json($comptes);
    }

    /**
     * Définit la valeur par défaut de solde_debiteur selon la classe SYSCOHADA.
     * Classes 2 (Immobilisations), 3 (Stocks), 5 (Trésorerie), 6 (Charges) = true.
     *
     * @param string $classe Le numéro de classe comptable
     * @return bool
     */
    private function defaultSoldeDebiteur(string $classe): bool
    {
        return in_array($classe, ['2', '3', '5', '6']);
    }
}
