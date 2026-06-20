<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeCourrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DaeCourriersController extends Controller
{
    public function index(Request $request)
    {
        $query = DaeCourrier::with(['client', 'traitePar', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('urgence')) {
            $query->where('urgence', $request->urgence);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_courrier', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_courrier', '<=', $request->date_fin);
        }
        $search = $request->input('search') ?? $request->input('recherche');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('objet', 'like', "%{$search}%")
                  ->orWhere('contenu', 'like', "%{$search}%");
            });
        }

        $courriers = $query->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($courriers);
        }

        return view('app', ['page' => 'dae-courriers']);
    }

    public function create()
    {
        return view('app', ['page' => 'dae-courriers-create']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'reference'    => 'nullable|string|max:100',
            'expediteur'   => 'nullable|string|max:255',
            'destinataire' => 'nullable|string|max:255',
            'type'         => 'required|in:entrant,sortant,interne',
            'mode'         => 'nullable|in:postal,email,remise_main',
            'objet'        => 'required|string|max:500',
            'contenu'      => 'nullable|string',
            'urgence'      => 'nullable|in:normal,urgent,tre_urgent',
            'date_courrier'=> 'nullable|date',
            'date_reception'=> 'nullable|date',
            'fichier_joint' => 'nullable|file|max:10240',
            'tags'         => 'nullable|json',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['statut'] = $validated['statut'] ?? 'brouillon';

        if (empty($validated['reference'])) {
            $validated['reference'] = 'CR-' . strtoupper(uniqid());
        }

        if ($request->hasFile('fichier_joint')) {
            $validated['fichier_joint'] = $request->file('fichier_joint')
                ->store('dae/courriers', 'public');
        }

        $courrier = DaeCourrier::create($validated);

        if ($request->expectsJson()) {
            return response()->json($courrier, 201);
        }

        return redirect()->route('dae.courriers.index')
            ->with('success', 'Courrier créé avec succès.');
    }

    public function show($id)
    {
        $courrier = DaeCourrier::with(['client', 'traitePar', 'createdBy'])
            ->findOrFail($id);

        if (request()->expectsJson()) {
            return response()->json($courrier);
        }

        return view('app', ['page' => 'dae-courriers-show']);
    }

    public function edit($id)
    {
        return view('app', ['page' => 'dae-courriers-edit']);
    }

    public function update(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);

        $validated = $request->validate([
            'client_id'     => 'sometimes|exists:clients,id',
            'reference'     => 'nullable|string|max:100',
            'expediteur'    => 'nullable|string|max:255',
            'destinataire'  => 'nullable|string|max:255',
            'type'          => 'sometimes|in:entrant,sortant,interne',
            'mode'          => 'nullable|in:postal,email,remise_main',
            'objet'         => 'sometimes|string|max:500',
            'contenu'       => 'nullable|string',
            'urgence'       => 'nullable|in:normal,urgent,tre_urgent',
            'statut'        => 'sometimes|in:brouillon,envoye,recu,traite,archive',
            'date_courrier' => 'nullable|date',
            'date_reception'=> 'nullable|date',
            'fichier_joint' => 'nullable|file|max:10240',
            'tags'          => 'nullable|json',
        ]);

        if ($request->hasFile('fichier_joint')) {
            if ($courrier->fichier_joint) {
                Storage::disk('public')->delete($courrier->fichier_joint);
            }
            $validated['fichier_joint'] = $request->file('fichier_joint')
                ->store('dae/courriers', 'public');
        }

        $courrier->update($validated);

        if ($request->expectsJson()) {
            return response()->json($courrier);
        }

        return redirect()->route('dae.courriers.index')
            ->with('success', 'Courrier mis à jour.');
    }

    public function destroy($id)
    {
        $courrier = DaeCourrier::findOrFail($id);
        if ($courrier->fichier_joint) {
            Storage::disk('public')->delete($courrier->fichier_joint);
        }
        $courrier->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Courrier supprimé.']);
        }

        return redirect()->route('dae.courriers.index')
            ->with('success', 'Courrier supprimé.');
    }

    public function traiter(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);

        $validated = $request->validate([
            'notes_traitement' => 'nullable|string',
        ]);

        $courrier->update([
            'statut'       => 'traite',
            'traite_par'   => Auth::id(),
            'date_traitement' => now(),
            'notes_traitement' => $validated['notes_traitement'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json($courrier);
        }

        return redirect()->back()->with('success', 'Courrier traité.');
    }

    public function archiver(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);
        $courrier->update(['statut' => 'archive']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Courrier archivé.']);
        }

        return redirect()->back()->with('success', 'Courrier archivé.');
    }

    public function dupliquer(Request $request, $id)
    {
        $original = DaeCourrier::findOrFail($id);
        $copy = $original->replicate(['reference', 'statut', 'traite_par', 'date_traitement', 'notes_traitement']);
        $copy->reference = 'CR-' . strtoupper(uniqid());
        $copy->statut = 'brouillon';
        $copy->created_by = Auth::id();
        $copy->save();

        if ($request->expectsJson()) {
            return response()->json($copy, 201);
        }

        return redirect()->route('dae.courriers.index')
            ->with('success', 'Courrier dupliqué.');
    }

    public function assigner(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $courrier->update([
            'assigned_to' => $validated['assigned_to'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Courrier assigné.', 'courrier' => $courrier]);
        }

        return redirect()->back()->with('success', 'Courrier assigné.');
    }

    public function repondre(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);

        $validated = $request->validate([
            'reponse' => 'required|string',
        ]);

        $courrier->update([
            'reponse' => $validated['reponse'],
            'statut'  => 'traite',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Réponse enregistrée.', 'courrier' => $courrier]);
        }

        return redirect()->back()->with('success', 'Réponse enregistrée.');
    }

    public function export($format)
    {
        $courriers = DaeCourrier::with('client')->get();

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="courriers.csv"',
            ];

            $callback = function () use ($courriers) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Référence', 'Type', 'Objet', 'Expéditeur', 'Statut', 'Date']);
                foreach ($courriers as $c) {
                    fputcsv($output, [
                        $c->reference, $c->type, $c->objet,
                        $c->expediteur, $c->statut,
                        $c->date_courrier?->format('d/m/Y'),
                    ]);
                }
                fclose($output);
            };

            return response()->stream($callback, 200, $headers);
        }

        return response()->json(['message' => 'Format non supporté.'], 400);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $path = $request->file('file')->store('dae/courriers', 'public');

        return response()->json([
            'path' => $path,
            'url'  => Storage::disk('public')->url($path),
        ]);
    }
}
