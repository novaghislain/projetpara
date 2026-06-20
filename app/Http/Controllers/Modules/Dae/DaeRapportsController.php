<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeRapport;
use Illuminate\Http\Request;

class DaeRapportsController extends Controller
{
    public function index(Request $request)
    {
        $query = DaeRapport::with('client')->orderBy('created_at', 'desc');

        if ($request->filled('type_rapport')) $query->where('type_rapport', $request->type_rapport);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('periode_debut')) $query->whereDate('periode_debut', '>=', $request->periode_debut);
        if ($request->filled('periode_fin')) $query->whereDate('periode_fin', '<=', $request->periode_fin);

        $rapports = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($rapports);
        return view('app', ['page' => 'dae-rapports']);
    }

    public function generer(Request $request)
    {
        $validated = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'titre'         => 'required|string|max:500',
            'type_rapport'   => 'required|string|max:200',
            'description'   => 'nullable|string',
            'periode_debut' => 'nullable|date',
            'periode_fin'   => 'nullable|date',
        ]);

        $validated['statut'] = 'brouillon';

        $rapport = DaeRapport::create($validated);

        if ($request->expectsJson()) return response()->json($rapport, 201);
        return redirect()->route('dae.rapports.index')->with('success', 'Rapport créé.');
    }

    public function show($id)
    {
        $rapport = DaeRapport::with('client')->findOrFail($id);
        if (request()->expectsJson()) return response()->json($rapport);
        return view('app', ['page' => 'dae-rapports-show']);
    }

    public function telecharger($id)
    {
        $rapport = DaeRapport::findOrFail($id);
        if (!$rapport->fichier) abort(404);
        return \Illuminate\Support\Facades\Storage::disk('public')->download($rapport->fichier);
    }

    public function destroy($id)
    {
        $rapport = DaeRapport::findOrFail($id);
        $rapport->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Rapport supprimé.']);
        return redirect()->route('dae.rapports.index')->with('success', 'Rapport supprimé.');
    }
}
