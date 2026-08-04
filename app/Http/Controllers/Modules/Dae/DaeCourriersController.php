<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeCourrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Contrôleur de gestion des courriers du module DAE.
 *
 * Permet la gestion complète des courriers entrants, sortants et internes
 * avec suivi, traitement, archivage, duplication et export.
 */
class DaeCourriersController extends BaseDaeController
{
    /**
     * Liste paginée des courriers avec filtres avancés.
     *
     * Filtres disponibles : type, statut, urgence, période, recherche
     * (référence, objet, contenu).
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
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
        $query->where('client_id', $this->getClientId($request));
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

    /**
     * Affiche le formulaire de création d'un courrier.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('app', ['page' => 'dae-courriers-create']);
    }

    /**
     * Crée un nouveau courrier avec gestion du fichier joint.
     *
     * @param Request $request La requête HTTP avec les données du courrier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $validated['client_id'] = $this->getClientId($request);

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

    /**
     * Affiche un courrier spécifique.
     *
     * @param int $id L'identifiant du courrier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        $courrier = DaeCourrier::with(['client', 'traitePar', 'createdBy'])
            ->findOrFail($id);

        if (request()->expectsJson()) {
            return response()->json($courrier);
        }

        return view('app', ['page' => 'dae-courriers-show']);
    }

    /**
     * Affiche le formulaire d'édition d'un courrier.
     *
     * @param int $id L'identifiant du courrier
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('app', ['page' => 'dae-courriers-edit']);
    }

    /**
     * Met à jour un courrier existant avec gestion du fichier joint.
     *
     * @param Request $request La requête HTTP avec les données de mise à jour
     * @param int $id L'identifiant du courrier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);

        $validated = $request->validate([
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

    /**
     * Supprime un courrier et son fichier joint associé.
     *
     * @param int $id L'identifiant du courrier à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
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

    /**
     * Traite un courrier en le marquant avec les notes de traitement.
     *
     * @param Request $request La requête HTTP avec les notes de traitement
     * @param int $id L'identifiant du courrier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
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

    /**
     * Archive un courrier.
     *
     * @param Request $request La requête HTTP
     * @param int $id L'identifiant du courrier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function archiver(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);
        $courrier->update(['statut' => 'archive']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Courrier archivé.']);
        }

        return redirect()->back()->with('success', 'Courrier archivé.');
    }

    /**
     * Duplique un courrier en créant une copie en statut brouillon.
     *
     * @param Request $request La requête HTTP
     * @param int $id L'identifiant du courrier à dupliquer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
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

    /**
     * Assigne un courrier à un utilisateur.
     *
     * @param Request $request La requête HTTP avec l'identifiant de l'utilisateur
     * @param int $id L'identifiant du courrier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
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

    /**
     * Enregistre une réponse à un courrier.
     *
     * @param Request $request La requête HTTP avec le texte de la réponse
     * @param int $id L'identifiant du courrier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
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

    /**
     * Exporte les courriers au format CSV.
     *
     * @param string $format Le format d'export (csv, etc.)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\StreamedResponse
     */
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

    /**
     * Télécharge un fichier joint pour un courrier.
     *
     * @param Request $request La requête HTTP contenant le fichier
     * @return \Illuminate\Http\JsonResponse
     */
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
