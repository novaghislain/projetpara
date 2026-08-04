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
     * Contrôleur pour le tableau de bord client.
     * Permet aux clients de consulter leurs commandes,
     * d'envoyer des messages et de télécharger les documents
     * associés à leurs commandes.
     */

    /**
     * Affiche la liste des commandes du client connecté.
     *
     * @return \Illuminate\View\View
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
     * Affiche le détail d'une commande client avec le pipeline,
     * les messages et les documents associés.
     *
     * @param int $id Identifiant de la commande
     * @return \Illuminate\View\View
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
     * Envoie un message dans le fil de discussion d'une commande.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Identifiant de la commande
     * @return \Illuminate\Http\RedirectResponse
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
     * Télécharge un document associé à une commande (facture, résultat, etc.).
     * Vérifie que le document appartient bien au client connecté.
     *
     * @param int $id Identifiant du document
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
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
