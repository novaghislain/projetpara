<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DaeMessagesController extends Controller
{
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

    public function show($id)
    {
        $message = DaeMessage::with('destinataire', 'createdBy')->findOrFail($id);

        // Marquer comme lu si c'était "recu"
        if ($message->statut === 'recu' && $message->destinataire_id === Auth::id()) {
            $message->update(['statut' => 'lu', 'lu_at' => now()]);
        }

        return response()->json($message);
    }

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

    public function destroy($id)
    {
        $message = DaeMessage::findOrFail($id);
        $message->delete();

        return response()->json(['message' => 'Message supprimé.']);
    }

    public function marquerLu($id)
    {
        $message = DaeMessage::findOrFail($id);
        $message->update(['statut' => 'lu', 'lu_at' => now()]);

        return response()->json($message);
    }

    public function marquerTraite($id)
    {
        $message = DaeMessage::findOrFail($id);
        $message->update(['statut' => 'traite', 'traite_at' => now()]);

        return response()->json($message);
    }

    public function archiver($id)
    {
        $message = DaeMessage::findOrFail($id);
        $message->update(['statut' => 'archive']);

        return response()->json($message);
    }

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
