<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeModeleCourrier;
use Illuminate\Http\Request;

class DaeModelesController extends BaseDaeController
{
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

    public function destroy($id)
    {
        $modele = DaeModeleCourrier::findOrFail($id);
        $modele->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Modèle supprimé.']);
        return redirect()->route('dae.modeles.index')->with('success', 'Modèle supprimé.');
    }

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
