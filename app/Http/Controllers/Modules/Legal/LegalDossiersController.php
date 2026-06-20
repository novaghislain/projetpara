<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalDossier;
use Illuminate\Http\Request;

class LegalDossiersController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-dossiers']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        $query = LegalDossier::byClient($clientId);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->priorite) {
            $query->where('priorite', $request->priorite);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-dossiers-create']);
        }
        $data = $request->validate([
            'client_id' => 'required|integer',
            'titre' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'priorite' => 'nullable|string',
        ]);

        $data['reference'] = 'DOS-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['created_by'] = auth()->id();

        $dossier = LegalDossier::create($data);

        return response()->json(['success' => true, 'data' => $dossier]);
    }

    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalDossier::findOrFail($id));
        }
        return view('app', ['page' => 'legal-dossiers-show']);
    }

    public function update(Request $request, $id)
    {
        $dossier = LegalDossier::findOrFail($id);
        $dossier->update($request->all());
        return response()->json(['success' => true, 'data' => $dossier]);
    }

    public function destroy($id)
    {
        LegalDossier::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function assign(Request $request, $id)
    {
        $dossier = LegalDossier::findOrFail($id);
        $dossier->update(['assigned_to' => $request->user_id]);
        return response()->json(['success' => true, 'data' => $dossier]);
    }

    public function changerStatut(Request $request, $id)
    {
        $dossier = LegalDossier::findOrFail($id);
        $dossier->update(['statut' => $request->statut]);
        return response()->json(['success' => true, 'data' => $dossier]);
    }

    public function addDocument(Request $request, $id)
    {
        $dossier = LegalDossier::findOrFail($id);
        $documents = $dossier->documents ?? [];
        $documents[] = [
            'nom' => $request->nom,
            'path' => $request->path,
            'date' => now()->format('Y-m-d'),
        ];
        $dossier->update(['documents' => $documents]);

        return response()->json(['success' => true]);
    }
}
