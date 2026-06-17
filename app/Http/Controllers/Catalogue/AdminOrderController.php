<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use App\Models\CatalogueOrder;
use App\Models\CatalogueOrderStatusHistory;
use App\Models\CatalogueOrderMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AdminOrderController extends Controller
{
    const STATUTS = [
        'Nouvelle Demande',
        'En cours',
        'En attente client',
        'Livrée',
        'Annulée',
    ];

    const ACTIVE_STATUTS = [
        'Nouvelle Demande',
        'En cours',
        'En attente client',
    ];

    const ARCHIVED_STATUTS = [
        'Livrée',
        'Annulée',
    ];

    /**
     * Vue Kanban de toutes les commandes
     */
    public function index()
    {
        $orders = CatalogueOrder::with(['service', 'category', 'client', 'responsable'])
            ->whereIn('statut', self::ACTIVE_STATUTS)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('statut');

        $kanban = [];
        foreach (self::ACTIVE_STATUTS as $statut) {
            $kanban[$statut] = $orders->get($statut, collect())->values();
        }

        $team = User::whereIn('role', ['admin', 'super_admin', 'collaborateur'])
            ->get(['id', 'name', 'email']);

        return view('app', [
            'page' => 'admin-orders-kanban',
            'props' => [
                'kanban'  => $kanban,
                'statuts' => self::ACTIVE_STATUTS,
                'team'    => $team,
            ]
        ]);
    }

    /**
     * Détail d'une commande (vue admin)
     */
    public function show($id)
    {
        $order = CatalogueOrder::with([
            'service', 'category', 'client', 'responsable',
            'messages.expediteur', 'documents', 'statusHistory'
        ])->findOrFail($id);

        return view('app', [
            'page' => 'admin-orders-show',
            'props' => [
                'order'   => $order,
                'team'    => User::whereIn('role', ['admin', 'super_admin', 'collaborateur'])
                                  ->get(['id', 'name', 'email']),
                'statuts' => self::STATUTS,
            ],
        ]);
    }

    /**
     * Vue des commandes archivées
     */
    public function archives()
    {
        $orders = CatalogueOrder::with(['service', 'category', 'client', 'responsable'])
            ->whereIn('statut', self::ARCHIVED_STATUTS)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('app', [
            'page' => 'admin-orders-archives',
            'props' => [
                'orders' => $orders,
            ],
        ]);
    }

    /**
     * Changer le statut d'une commande (drag & drop Kanban)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'statut'      => 'required|string|in:' . implode(',', self::STATUTS),
            'commentaire' => 'nullable|string',
        ]);

        $order = CatalogueOrder::findOrFail($id);
        $previous = $order->statut;

        $order->update(['statut' => $request->statut]);

        CatalogueOrderStatusHistory::create([
            'commande_id'      => $order->id,
            'statut_precedent' => $previous,
            'statut_nouveau'   => $request->statut,
            'id_user'          => Auth::id(),
            'commentaire'      => $request->commentaire,
        ]);

        return response()->json([
            'success' => true,
            'order'   => $order->fresh(['service', 'client', 'responsable']),
        ]);
    }

    /**
     * Assigner un responsable à une commande
     */
    public function assignResponsable(Request $request, $id)
    {
        $request->validate(['responsable_id' => 'required|exists:users,id']);
        $order = CatalogueOrder::findOrFail($id);
        $order->update(['responsable_id' => $request->responsable_id]);

        return response()->json(['success' => true, 'order' => $order->fresh('responsable')]);
    }

    /**
     * Envoyer un message interne (réponse admin)
     */
    public function storeMessage(Request $request, $id)
    {
        $request->validate(['contenu' => 'required|string']);

        $order = CatalogueOrder::findOrFail($id);

        CatalogueOrderMessage::create([
            'commande_id'  => $order->id,
            'expediteur_id' => Auth::id(),
            'type'         => 'equipe',
            'contenu'      => $request->contenu,
        ]);

        return redirect()->back()->with('success', 'Message envoyé.');
    }

    /**
     * Mettre à jour les notes internes + délai + montant
     */
    public function updateDetails(Request $request, $id)
    {
        $request->validate([
            'notes_internes'       => 'nullable|string',
            'delai_estime'         => 'nullable|string',
            'montant_estime_fcfa'  => 'nullable|numeric',
            'date_livraison'       => 'nullable|date',
        ]);

        $order = CatalogueOrder::findOrFail($id);
        $order->update($request->only(
            'notes_internes', 'delai_estime', 'montant_estime_fcfa', 'date_livraison'
        ));

        return response()->json(['success' => true]);
    }

    /**
     * Uploader un document final (facture, résultat)
     */
    public function storeDocument(Request $request, $id)
    {
        $request->validate([
            'fichier' => 'required|file|max:10240', // 10MB max
            'type'    => 'required|in:facture,resultat,autre',
            'titre'   => 'nullable|string|max:255',
        ]);

        $order = CatalogueOrder::findOrFail($id);

        $file = $request->file('fichier');
        $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('catalogue_documents/' . $order->id, $filename, 'public');

        \App\Models\CatalogueOrderDocument::create([
            'commande_id'      => $order->id,
            'type'             => $request->type,
            'nom_fichier'      => $request->titre ?: $file->getClientOriginalName(),
            'chemin_stockage'  => $path,
            'taille_ko'        => intval($file->getSize() / 1024),
            'id_user'          => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document ajouté avec succès.');
    }
}
