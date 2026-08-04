<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeModeleCourrier;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des modèles de courriers du module DAE.
 *
 * Permet la création, modification et génération de modèles
 * de courriers avec substitution de variables.
 */
class DaeModelesController extends BaseDaeController
{
    /**
     * Liste paginée des modèles avec filtres.
     *
     * Filtres disponibles : type, categorie, recherche (nom, objet_defaut).
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = DaeModeleCourrier::query()->orderBy('nom');

        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('categorie')) $query->where('categorie', $request->categorie);
        if ($request->filled('recherche')) {
            $s = $request->recherche;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%{$s}%")
                  ->orWhere('objet_defaut', 'like', "%{$s}%");
            });
        }

        $modeles = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($modeles);
        return view('app', ['page' => 'dae-modeles']);
    }

    /**
     * Crée un nouveau modèle de courrier.
     *
     * @param Request $request La requête HTTP avec les données du modèle
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'   => 'nullable|exists:clients,id',
            'nom'         => 'required|string|max:255',
            'type'        => 'required|string|max:200',
            'objet_defaut'=> 'nullable|string|max:500',
            'corps'       => 'nullable|string',
            'variables'   => 'nullable|json',
            'categorie'   => 'nullable|string|max:200',
        ]);

        $modele = DaeModeleCourrier::create($validated);

        if ($request->expectsJson()) return response()->json($modele, 201);
        return redirect()->route('dae.modeles.index')->with('success', 'Modèle créé.');
    }

    /**
     * Met à jour un modèle de courrier existant.
     *
     * @param Request $request La requête HTTP avec les données de mise à jour
     * @param int $id L'identifiant du modèle
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $modele = DaeModeleCourrier::findOrFail($id);

        $validated = $request->validate([
            'nom'          => 'sometimes|string|max:255',
            'type'         => 'sometimes|string|max:200',
            'objet_defaut' => 'nullable|string|max:500',
            'corps'        => 'nullable|string',
            'variables'    => 'nullable|json',
            'categorie'    => 'nullable|string|max:200',
        ]);

        $modele->update($validated);

        if ($request->expectsJson()) return response()->json($modele);
        return redirect()->route('dae.modeles.index')->with('success', 'Modèle mis à jour.');
    }

    /**
     * Supprime un modèle de courrier.
     *
     * @param int $id L'identifiant du modèle à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $modele = DaeModeleCourrier::findOrFail($id);
        $modele->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Modèle supprimé.']);
        return redirect()->route('dae.modeles.index')->with('success', 'Modèle supprimé.');
    }

    /**
     * Génère le contenu d'un modèle avec substitution des variables.
     *
     * Remplace les placeholders {{variable}} par les valeurs fournies.
     *
     * @param Request $request La requête HTTP avec les variables de substitution
     * @param int $id L'identifiant du modèle
     * @return \Illuminate\Http\JsonResponse
     */
    public function generer(Request $request, $id)
    {
        $modele = DaeModeleCourrier::findOrFail($id);

        $request->validate([
            'variables' => 'nullable|array',
        ]);

        $corps = $modele->corps;
        $objet = $modele->objet_defaut;

        if ($request->filled('variables')) {
            foreach ($request->variables as $key => $value) {
                $placeholder = "{{" . $key . "}}";
                $corps = str_replace($placeholder, $value, $corps);
                $objet = str_replace($placeholder, $value, $objet);
            }
        }

        return response()->json([
            'objet' => $objet,
            'corps' => $corps,
        ]);
    }
}
