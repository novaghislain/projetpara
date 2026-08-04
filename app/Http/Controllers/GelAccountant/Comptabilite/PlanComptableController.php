<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion du plan comptable d'un cabinet.
 *
 * Permet de consulter, créer et modifier les comptes comptables rattachés
 * à un cabinet. Les comptes peuvent être filtrés par classe et par
 * recherche textuelle sur le code ou l'intitulé.
 */
class PlanComptableController extends Controller
{
    /**
     * Affiche la liste des comptes du plan comptable.
     *
     * Les comptes sont filtrés par cabinet et peuvent être affinés
     * par classe comptable ou par recherche (code / intitulé). La liste
     * des clients actifs est également transmise à la vue pour les
     * filtres croisés.
     *
     * @param  Request $request La requête avec les filtres optionnels
     *                          `classe` et `search`.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? 1;

        $query = CompteComptable::where('cabinet_id', $cabinetId);

        // Filtre par classe comptable (ex. classe 1, classe 2, ...)
        if ($classe = $request->input('classe')) {
            $query->where('classe', $classe);
        }

        // Recherche textuelle sur le code ou l'intitulé du compte
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('intitule', 'like', "%{$search}%");
            });
        }

        $comptes = $query->orderBy('code')->get();
        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        return view('gel-accountant.comptabilite.plan-comptable.index', compact('comptes', 'clients') + ['currentSection' => 'comptabilite', 'currentPage' => 'plan-comptable']);
    }

    /**
     * Crée un nouveau compte dans le plan comptable.
     *
     * Rattache automatiquement le compte au cabinet de l'utilisateur
     * et calcule le niveau hiérarchique à partir de la longueur du code.
     *
     * @param  Request $request La requête contenant les données du compte.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'intitule' => 'required|string|max:255',
            'type' => 'nullable|string|max:20',
            'code_parent' => 'nullable|string|max:20',
        ]);

        $firstDigit = substr($validated['code'], 0, 1);
        if (!is_numeric($firstDigit) || $firstDigit < 1 || $firstDigit > 9) {
            return back()->withErrors(['code' => 'Le code doit commencer par un chiffre entre 1 et 9 selon le plan SYSCOHADA.'])->withInput();
        }

        $validated['classe'] = $firstDigit;
        $validated['cabinet_id'] = $user->cabinet_id ?? 1;
        // Le niveau hiérarchique est déduit de la longueur du code
        // (ex. code "6" -> niveau 0, code "60" -> niveau 1, etc.)
        $validated['niveau'] = strlen($validated['code']) - 1;

        CompteComptable::create($validated);

        return redirect()->route('gel-accountant.comptabilite.plan-comptable')
            ->with('success', 'Compte créé avec succès.');
    }

    /**
     * Met à jour un compte existant du plan comptable.
     *
     * @param  Request $request La requête contenant les champs modifiés.
     * @param  int     $id      L'identifiant du compte à mettre à jour.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $compte = CompteComptable::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'intitule' => 'required|string|max:255',
            'type' => 'nullable|string|max:20',
            'actif' => 'nullable|boolean',
        ]);

        $validated['actif'] = $request->boolean('actif');
        $compte->update($validated);

        return redirect()->route('gel-accountant.comptabilite.plan-comptable')
            ->with('success', 'Compte mis à jour.');
    }

    /**
     * Importe le plan comptable standard SYSCOHADA.
     */
    public function importSyscohada(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? 1;

        $comptesSyscohada = [
            ['code' => '101', 'intitule' => 'Capital social', 'type' => 'passif', 'classe' => 1, 'niveau' => 2],
            ['code' => '131', 'intitule' => 'Résultat net', 'type' => 'passif', 'classe' => 1, 'niveau' => 2],
            ['code' => '161', 'intitule' => 'Emprunts', 'type' => 'passif', 'classe' => 1, 'niveau' => 2],
            ['code' => '211', 'intitule' => 'Frais de développement', 'type' => 'actif', 'classe' => 2, 'niveau' => 2],
            ['code' => '241', 'intitule' => 'Matériel et outillage', 'type' => 'actif', 'classe' => 2, 'niveau' => 2],
            ['code' => '244', 'intitule' => 'Matériel de transport', 'type' => 'actif', 'classe' => 2, 'niveau' => 2],
            ['code' => '245', 'intitule' => 'Matériel informatique', 'type' => 'actif', 'classe' => 2, 'niveau' => 2],
            ['code' => '311', 'intitule' => 'Marchandises', 'type' => 'actif', 'classe' => 3, 'niveau' => 2],
            ['code' => '401', 'intitule' => 'Fournisseurs', 'type' => 'passif', 'classe' => 4, 'niveau' => 2],
            ['code' => '411', 'intitule' => 'Clients', 'type' => 'actif', 'classe' => 4, 'niveau' => 2],
            ['code' => '421', 'intitule' => 'Personnel - Avances', 'type' => 'actif', 'classe' => 4, 'niveau' => 2],
            ['code' => '422', 'intitule' => 'Personnel - Rémunérations dues', 'type' => 'passif', 'classe' => 4, 'niveau' => 2],
            ['code' => '431', 'intitule' => 'Sécurité sociale', 'type' => 'passif', 'classe' => 4, 'niveau' => 2],
            ['code' => '443', 'intitule' => 'État, TVA facturée', 'type' => 'passif', 'classe' => 4, 'niveau' => 2],
            ['code' => '444', 'intitule' => 'État, impôts sur les bénéfices', 'type' => 'passif', 'classe' => 4, 'niveau' => 2],
            ['code' => '445', 'intitule' => 'État, TVA récupérable', 'type' => 'actif', 'classe' => 4, 'niveau' => 2],
            ['code' => '521', 'intitule' => 'Banques', 'type' => 'actif', 'classe' => 5, 'niveau' => 2],
            ['code' => '571', 'intitule' => 'Caisse', 'type' => 'actif', 'classe' => 5, 'niveau' => 2],
            ['code' => '601', 'intitule' => 'Achats de marchandises', 'type' => 'charge', 'classe' => 6, 'niveau' => 2],
            ['code' => '605', 'intitule' => 'Autres achats', 'type' => 'charge', 'classe' => 6, 'niveau' => 2],
            ['code' => '613', 'intitule' => 'Locations', 'type' => 'charge', 'classe' => 6, 'niveau' => 2],
            ['code' => '622', 'intitule' => 'Rémunérations d\'intermédiaires et honoraires', 'type' => 'charge', 'classe' => 6, 'niveau' => 2],
            ['code' => '631', 'intitule' => 'Frais bancaires', 'type' => 'charge', 'classe' => 6, 'niveau' => 2],
            ['code' => '661', 'intitule' => 'Rémunérations du personnel', 'type' => 'charge', 'classe' => 6, 'niveau' => 2],
            ['code' => '701', 'intitule' => 'Ventes de marchandises', 'type' => 'produit', 'classe' => 7, 'niveau' => 2],
            ['code' => '706', 'intitule' => 'Services vendus', 'type' => 'produit', 'classe' => 7, 'niveau' => 2],
        ];

        $now = now();
        $insertData = array_map(function($c) use ($cabinetId, $now) {
            $c['cabinet_id'] = $cabinetId;
            $c['created_at'] = $now;
            $c['updated_at'] = $now;
            return $c;
        }, $comptesSyscohada);

        CompteComptable::withoutGlobalScopes()->upsert(
            $insertData,
            ['cabinet_id', 'code'],
            ['intitule', 'type', 'classe', 'niveau', 'updated_at']
        );

        return redirect()->route('gel-accountant.comptabilite.plan-comptable')
            ->with('success', 'Plan comptable SYSCOHADA importé avec succès.');
    }
}
