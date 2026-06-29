<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DaeEmailsController extends BaseDaeController
{
    public function index(Request $request)
    {
        $query = DaeEmail::with('client');

        // Map sidebar dossiers to statut filters
        if ($request->filled('dossier')) {
            match ($request->dossier) {
                'reception' => $query->whereIn('statut', ['recu', 'lu']),
                'envoyes'   => $query->where('statut', 'envoye'),
                'brouillons' => $query->where('statut', 'brouillon'),
                'archive'   => $query->where('statut', 'archive'),
                'corbeille' => $query->onlyTrashed(),
                default     => $query->where('dossier', $request->dossier),
            };
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        $query->where('client_id', $this->getClientId($request));
        $search = $request->input('search') ?? $request->input('recherche');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('objet', 'like', "%{$search}%")
                  ->orWhere('corps_texte', 'like', "%{$search}%");
            });
        }

        $emails = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->expectsJson()) return response()->json($emails);
        return view('app', ['page' => 'dae-emails']);
    }

    public function create()
    {
        return view('app', ['page' => 'dae-emails-create']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_address'  => 'required|email|max:255',
            'to_addresses'  => 'required|json',
            'cc_addresses'  => 'nullable|json',
            'objet'         => 'required|string|max:500',
            'corps_html'    => 'nullable|string',
            'corps_texte'   => 'nullable|string',
            'pieces_jointes'=> 'nullable|json',
            'dossier'       => 'nullable|string|max:100',
            'tags'          => 'nullable|json',
        ]);

        $validated['statut'] = 'brouillon';
        $validated['reference_message'] = 'EM-' . strtoupper(uniqid());
        $validated['client_id'] = $this->getClientId($request);

        $email = DaeEmail::create($validated);

        if ($request->expectsJson()) return response()->json($email, 201);
        return redirect()->route('dae.emails.index')->with('success', 'Email créé.');
    }

    public function show($id)
    {
        $email = DaeEmail::with('client', 'reponses')->findOrFail($id);
        if ($email->statut === 'recu') {
            $email->update(['statut' => 'lu']);
        }
        if (request()->expectsJson()) return response()->json($email);
        return view('app', ['page' => 'dae-emails-show']);
    }

    public function destroy($id)
    {
        $email = DaeEmail::findOrFail($id);
        $email->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Email supprimé.']);
        return redirect()->route('dae.emails.index')->with('success', 'Email supprimé.');
    }

    public function marquerLu(Request $request, $id)
    {
        $email = DaeEmail::findOrFail($id);
        $email->update(['statut' => 'lu']);

        if ($request->expectsJson()) return response()->json($email);
        return redirect()->back();
    }

    public function repondre(Request $request, $id)
    {
        $original = DaeEmail::findOrFail($id);

        $validated = $request->validate([
            'corps_html'   => 'nullable|string',
            'corps_texte'  => 'nullable|string',
            'pieces_jointes' => 'nullable|json',
        ]);

        $reponse = DaeEmail::create([
            'client_id'    => $this->getClientId($request),
            'from_address' => Auth::user()->email,
            'to_addresses' => json_encode([$original->from_address]),
            'objet'        => 'Re: ' . $original->objet,
            'corps_html'   => $validated['corps_html'] ?? null,
            'corps_texte'  => $validated['corps_texte'] ?? null,
            'pieces_jointes' => $validated['pieces_jointes'] ?? null,
            'statut'       => 'brouillon',
            'dossier'      => 'envoyes',
            'reponse_a_id' => $original->id,
            'reference_message' => 'EM-' . strtoupper(uniqid()),
        ]);

        if ($request->expectsJson()) return response()->json($reponse, 201);
        return redirect()->route('dae.emails.index')->with('success', 'Réponse créée.');
    }

    public function classer(Request $request, $id)
    {
        $request->validate(['dossier' => 'required|string|max:100']);
        $email = DaeEmail::findOrFail($id);

        $update = ['dossier' => $request->dossier];
        if ($request->dossier === 'archive') {
            $update['statut'] = 'archive';
        }

        $email->update($update);

        if ($request->expectsJson()) return response()->json($email);
        return redirect()->back()->with('success', 'Email classé.');
    }

    public function archiver(Request $request, $id)
    {
        $email = DaeEmail::findOrFail($id);
        $email->update(['statut' => 'archive', 'dossier' => 'archive']);

        if ($request->expectsJson()) return response()->json(['message' => 'Email archivé.']);
        return redirect()->back()->with('success', 'Email archivé.');
    }

    public function stats(Request $request)
    {
        $query = DaeEmail::where('client_id', $this->getClientId($request));

        return response()->json([
            'total'     => $query->count(),
            'recus'     => (clone $query)->where('statut', 'recu')->count(),
            'lus'       => (clone $query)->where('statut', 'lu')->count(),
            'envoyes'   => (clone $query)->where('statut', 'envoye')->count(),
            'brouillons' => (clone $query)->where('statut', 'brouillon')->count(),
            'archives'  => (clone $query)->where('statut', 'archive')->count(),
        ]);
    }
}
