<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalRegistre;
use Illuminate\Http\Request;

class LegalRegistresController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-registres']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        return response()->json(
            LegalRegistre::byClient($clientId)->orderBy('type')->orderBy('annee', 'desc')->get()
        );
    }

    public function show($type, $annee, Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-registres-show']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        $registre = LegalRegistre::byClient($clientId)
            ->where('type', $type)
            ->where('annee', $annee)
            ->firstOrFail();

        return response()->json($registre);
    }

    public function addEntry(Request $request, $type, $annee)
    {
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);

        $registre = LegalRegistre::firstOrCreate(
            [
                'client_id' => $clientId,
                'type' => $type,
                'annee' => $annee,
            ],
            ['entrees' => []]
        );

        $entrees = $registre->entrees ?? [];
        $entrees[] = [
            'date' => now()->format('Y-m-d'),
            'numero' => count($entrees) + 1,
            'objet' => $request->objet,
            'details' => $request->details,
        ];

        $registre->update(['entrees' => $entrees]);

        return response()->json(['success' => true, 'data' => $registre]);
    }

    public function export($type, $annee, Request $request)
    {
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        $registre = LegalRegistre::byClient($clientId)
            ->where('type', $type)
            ->where('annee', $annee)
            ->firstOrFail();

        return response()->json($registre);
    }
}
