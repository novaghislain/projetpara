<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCompliance;
use Illuminate\Http\Request;

class LegalComplianceController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-conformite']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        $query = LegalCompliance::byClient($clientId);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderBy('date_echeance')->get());
    }

    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-conformite-create']);
        }
        $data = $request->validate([
            'client_id' => 'required|integer',
            'intitule' => 'required|string|max:255',
            'type' => 'required|string',
            'organisme' => 'required|string',
            'periodicite' => 'required|string',
            'date_echeance' => 'required|date',
        ]);

        $data['created_by'] = auth()->id();
        $item = LegalCompliance::create($data);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalCompliance::findOrFail($id));
        }
        return view('app', ['page' => 'legal-conformite-show']);
    }

    public function update(Request $request, $id)
    {
        $item = LegalCompliance::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        LegalCompliance::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function verifier($id)
    {
        $item = LegalCompliance::findOrFail($id);
        $item->update([
            'statut' => 'conforme',
            'date_derniere_conformite' => now(),
        ]);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function calendrier(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-conformite-calendrier']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        $items = LegalCompliance::byClient($clientId)
            ->whereYear('date_echeance', $request->annee ?? date('Y'))
            ->orderBy('date_echeance')
            ->get();

        return response()->json($items);
    }
}
