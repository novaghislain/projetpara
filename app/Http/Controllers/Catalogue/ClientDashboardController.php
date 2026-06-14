<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use App\Models\CatalogueOrder;
use App\Models\CatalogueOrderMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ClientDashboardController extends Controller
{
    /**
     * Liste des commandes du client
     */
    public function index()
    {
        $orders = CatalogueOrder::with(['service', 'category'])
            ->where('client_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('app', [
            'page' => 'client-orders-index',
            'props' => ['orders' => $orders]
        ]);
    }

    /**
     * Détail d'une commande (Pipeline, Messages, Documents)
     */
    public function show($id)
    {
        $order = CatalogueOrder::with([
            'service', 
            'category', 
            'documents', 
            'messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            },
            'messages.expediteur',
            'statusHistory' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ])
        ->where('client_id', Auth::id())
        ->findOrFail($id);

        return view('app', [
            'page' => 'client-orders-show',
            'props' => ['order' => $order]
        ]);
    }

    /**
     * Envoi d'un message dans la commande
     */
    public function storeMessage(Request $request, $id)
    {
        $request->validate([
            'contenu' => 'required|string',
            // fichier_joint validation can be added here
        ]);

        $order = CatalogueOrder::where('client_id', Auth::id())->findOrFail($id);

        CatalogueOrderMessage::create([
            'commande_id' => $order->id,
            'expediteur_id' => Auth::id(),
            'type' => 'client',
            'contenu' => $request->contenu,
        ]);

        return redirect()->back()->with('success', 'Message envoyé.');
    }

    /**
     * Téléchargement d'un document final (facture, résultat)
     */
    public function downloadDocument($id)
    {
        $document = \App\Models\CatalogueOrderDocument::whereHas('order', function ($query) {
            $query->where('client_id', Auth::id());
        })->findOrFail($id);

        return response()->download(
            storage_path('app/public/' . $document->chemin_stockage),
            $document->nom_fichier
        );
    }
}
