<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalAssembly;
use App\Services\Legal\AGService;
use Illuminate\Http\Request;

class LegalAssembliesController extends Controller
{
    protected AGService $agService;

    public function __construct(AGService $agService)
    {
        $this->agService = $agService;
    }

    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-assemblees']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        return response()->json(
            LegalAssembly::byClient($clientId)->orderBy('date_tenue', 'desc')->get()
        );
    }

    public function create()
    {
        return view('app', ['page' => 'legal-assemblees-create']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|integer',
            'type' => 'required|string',
            'annee' => 'required|integer',
            'date_tenue' => 'required|date',
            'lieu' => 'required|string',
            'ordre_du_jour' => 'required|array',
        ]);

        $data['created_by'] = auth()->id();
        $ag = $this->agService->preparerAG($data);

        return response()->json(['success' => true, 'data' => $ag]);
    }

    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalAssembly::findOrFail($id));
        }
        return view('app', ['page' => 'legal-assemblees-show']);
    }

    public function edit($id)
    {
        return view('app', ['page' => 'legal-assemblees-edit']);
    }

    public function update(Request $request, $id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $ag->update($request->all());
        return response()->json(['success' => true, 'data' => $ag]);
    }

    public function destroy($id)
    {
        LegalAssembly::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function genererConvocation($id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $contenu = $this->agService->acteGenerator->genererConvocationAG($ag);
        return response()->json(['html' => $contenu]);
    }

    public function enregistrerPresences(Request $request, $id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $ag->update([
            'participants' => $request->participants,
            'quorum_atteint' => $request->quorum_atteint,
        ]);
        return response()->json(['success' => true, 'data' => $ag]);
    }

    public function saisirResolutions(Request $request, $id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $ag->update(['resolutions' => $request->resolutions]);
        return response()->json(['success' => true]);
    }

    public function genererPV($id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $path = $this->agService->genererPV($ag, $ag->resolutions ?? []);
        $this->agService->enregistrerAuRegistre($ag);
        return response()->json(['success' => true, 'pv_path' => $path]);
    }

    public function approuverPV(Request $request, $id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $ag->update(['pv_approuve' => true]);
        return response()->json(['success' => true]);
    }
}
