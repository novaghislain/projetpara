<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalActsLibrary;
use App\Services\Legal\ActeGeneratorService;
use Illuminate\Http\Request;

class LegalActsLibraryController extends BaseLegalController
{
    protected ActeGeneratorService $acteGenerator;

    public function __construct(ActeGeneratorService $acteGenerator)
    {
        $this->acteGenerator = $acteGenerator;
    }

    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-bibliotheque']);
        }
        // Super admin voit tous les modèles, sinon filtrer par client
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            $modeles = LegalActsLibrary::orderBy('categorie')->get();
        } else {
            $clientId = $this->getClientId($request);
            $modeles = LegalActsLibrary::whereNull('client_id')
                ->orWhere('client_id', $clientId)
                ->orderBy('categorie')
                ->get();
        }

        return response()->json($modeles);
    }

    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-bibliotheque-create']);
        }
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'categorie' => 'required|string',
            'contenu' => 'required|string',
            'variables' => 'nullable|array',
            'is_public' => 'nullable|boolean',
        ]);

        $data['created_by'] = auth()->id();
        $modele = LegalActsLibrary::create($data);

        return response()->json(['success' => true, 'data' => $modele]);
    }

    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalActsLibrary::findOrFail($id));
        }
        return view('app', ['page' => 'legal-bibliotheque-show']);
    }

    public function edit($id)
    {
        return view('app', ['page' => 'legal-bibliotheque-edit']);
    }

    public function update(Request $request, $id)
    {
        $modele = LegalActsLibrary::findOrFail($id);
        $modele->update($request->all());
        return response()->json(['success' => true, 'data' => $modele]);
    }

    public function destroy($id)
    {
        LegalActsLibrary::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function generer(Request $request, $id)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-bibliotheque-generer']);
        }
        $html = $this->acteGenerator->generer(
            $id,
            $request->variables ?? [],
            $this->getClientId($request)
        );

        return response()->json(['html' => $html]);
    }

    public function preview($id)
    {
        $modele = LegalActsLibrary::findOrFail($id);
        $variables = $this->acteGenerator->getVariablesRequises($id);

        return response()->json([
            'modele' => $modele,
            'variables' => $variables,
        ]);
    }
}
