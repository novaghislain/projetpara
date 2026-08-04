<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des messages du module DAE.
 *
 * Permet de gérer les messages, appels, notes et informations
 * avec des fonctionnalités de filtrage, suivi et statistiques.
 */
class DaeMessagesController extends Controller
{
    /**
     * Liste paginée des messages avec filtres.
     *
     * Retourne une vue ou une réponse JSON selon le type de requête.
     * Les filtres disponibles : type, statut, urgence, client_id.
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'dae-messages']);
        }

        $user = Auth::user();
        $query = DaeMessage::with('destinataire', 'createdBy');

        // Filtre par client assigné ou super admin
        if (!$user->isSuperAdmin()) {
            $clientIds = $user->clients_assignes ?? [];
            $query->whereIn('client_id', $clientIds);
        }

        // Filtres
        if ($request->filled('type')) {
            $query->byType($request->type);
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

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate(20)
        );
    }

    /**
     * Crée un nouveau message.
     *
     * Valide les données entrantes et enregistre le message avec
     * le statut "recu" et l'utilisateur connecté comme créateur.
     *
     * @param Request $request La requête HTTP contenant les données du message
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:appel,message,note,info',
            'expediteur_name' => 'nullable|string|max:255',
            'expediteur_entreprise' => 'nullable|string|max:255',
            'expediteur_contact' => 'nullable|string|max:255',
            'destinataire_id' => 'nullable|exists:users,id',
            'objet' => 'required|string|max:500',
            'contenu' => 'nullable|string',
            'urgence' => 'nullable|in:normal,urgent',
            'appel_rappele' => 'nullable|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['statut'] = 'recu';

        $message = DaeMessage::create($validated);

        return response()->json($message->load('destinataire', 'createdBy'), 201);
    }

    /**
     * Affiche un message spécifique.
     *
     * Marque le message comme "lu" si le destinataire est l'utilisateur connecté.
     *
     * @param int $id L'identifiant du message
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $message = DaeMessage::with('destinataire', 'createdBy')->findOrFail($id);

        // Marquer comme lu si c'était "recu"
        if ($message->statut === 'recu' && $message->destinataire_id === Auth::id()) {
            $message->update(['statut' => 'lu', 'lu_at' => now()]);
        }

        return response()->json($message);
    }

    /**
     * Met à jour un message existant.
     *
     * Permet de modifier les champs du message et de changer son statut.
     * Si le statut passe à "traite", la date de traitement est enregistrée.
     *
     * @param Request $request La requête HTTP avec les données de mise à jour
     * @param int $id L'identifiant du message
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $message = DaeMessage::findOrFail($id);

        $validated = $request->validate([
            'type' => 'sometimes|in:appel,message,note,info',
            'expediteur_name' => 'nullable|string|max:255',
            'expediteur_entreprise' => 'nullable|string|max:255',
            'expediteur_contact' => 'nullable|string|max:255',
            'destinataire_id' => 'nullable|exists:users,id',
            'objet' => 'sometimes|string|max:500',
            'contenu' => 'nullable|string',
            'urgence' => 'nullable|in:normal,urgent',
            'statut' => 'sometimes|in:recu,lu,traite,archive',
        ]);

        if (isset($validated['statut']) && $validated['statut'] === 'traite' && !$message->traite_at) {
            $validated['traite_at'] = now();
        }

        $message->update($validated);

        return response()->json($message->load('destinataire', 'createdBy'));
    }

    /**
     * Supprime un message.
     *
     * @param int $id L'identifiant du message à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $message = DaeMessage::findOrFail($id);
        $message->delete();

        return response()->json(['message' => 'Message supprimé.']);
    }

    /**
     * Marque un message comme lu.
     *
     * @param int $id L'identifiant du message
     * @return \Illuminate\Http\JsonResponse
     */
    public function marquerLu($id)
    {
        $message = DaeMessage::findOrFail($id);
        $message->update(['statut' => 'lu', 'lu_at' => now()]);

        return response()->json($message);
    }

    /**
     * Marque un message comme traité.
     *
     * @param int $id L'identifiant du message
     * @return \Illuminate\Http\JsonResponse
     */
    public function marquerTraite($id)
    {
        $message = DaeMessage::findOrFail($id);
        $message->update(['statut' => 'traite', 'traite_at' => now()]);

        return response()->json($message);
    }

    /**
     * Archive un message.
     *
     * @param int $id L'identifiant du message
     * @return \Illuminate\Http\JsonResponse
     */
    public function archiver($id)
    {
        $message = DaeMessage::findOrFail($id);
        $message->update(['statut' => 'archive']);

        return response()->json($message);
    }

    /**
     * Retourne les statistiques des messages.
     *
     * Calcule le total, les non lus, les urgents et les appels en attente.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $user = Auth::user();
        $clientIds = $user->isSuperAdmin() ? null : ($user->clients_assignes ?? []);

        $query = fn($model) => $clientIds
            ? $model->whereIn('client_id', $clientIds)
            : $model;

        return response()->json([
            'total' => $query(DaeMessage::query())->count(),
            'non_lus' => $query(DaeMessage::query())->nonLus()->count(),
            'urgents' => $query(DaeMessage::query())->urgents()->count(),
            'appels' => $query(DaeMessage::query())->byType('appel')->whereIn('statut', ['recu', 'lu'])->count(),
        ]);
    }
}
