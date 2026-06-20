<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalLitigation;
use Illuminate\Http\Request;

class LegalLitigationsController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-contentieux']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        $query = LegalLitigation::byClient($clientId);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-contentieux-create']);
        }
        $data = $request->validate([
            'client_id' => 'required|integer',
            'titre' => 'required|string|max:255',
            'type' => 'required|string',
            'nature' => 'required|string',
            'partie_adverse' => 'required|string',
            'tribunal' => 'required|string',
        ]);

        $data['reference'] = 'LIT-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['created_by'] = auth()->id();

        $litige = LegalLitigation::create($data);

        return response()->json(['success' => true, 'data' => $litige]);
    }

    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalLitigation::findOrFail($id));
        }
        return view('app', ['page' => 'legal-contentieux-show']);
    }

    public function update(Request $request, $id)
    {
        $litige = LegalLitigation::findOrFail($id);
        $litige->update($request->all());
        return response()->json(['success' => true, 'data' => $litige]);
    }

    public function destroy($id)
    {
        LegalLitigation::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function addHistorique(Request $request, $id)
    {
        $litige = LegalLitigation::findOrFail($id);
        $historique = $litige->historique ?? [];
        $historique[] = [
            'date' => now()->format('Y-m-d H:i:s'),
            'action' => $request->action,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
        ];
        $litige->update(['historique' => $historique]);

        return response()->json(['success' => true, 'data' => $litige]);
    }

    public function addDocument(Request $request, $id)
    {
        $litige = LegalLitigation::findOrFail($id);
        $documents = $litige->documents ?? [];
        $documents[] = [
            'nom' => $request->nom,
            'path' => $request->path,
            'date' => now()->format('Y-m-d'),
        ];
        $litige->update(['documents' => $documents]);

        return response()->json(['success' => true]);
    }

    public function changerStatut(Request $request, $id)
    {
        $litige = LegalLitigation::findOrFail($id);
        $litige->update(['statut' => $request->statut]);

        // Ajouter au journal
        $historique = $litige->historique ?? [];
        $historique[] = [
            'date' => now()->format('Y-m-d H:i:s'),
            'action' => 'Changement de statut → ' . $request->statut,
            'user_id' => auth()->id(),
        ];
        $litige->update(['historique' => $historique]);

        return response()->json(['success' => true, 'data' => $litige]);
    }
}
