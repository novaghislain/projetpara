<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use App\Models\CatalogueService;
use App\Models\CatalogueOrder;
use App\Models\CatalogueOrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Contrôleur pour la gestion des commandes côté public et client.
     * Gère le parcours de commande : préparation en session,
     * formulaire Wizard, soumission avec gestion du panier
     * et des documents associés.
     */

    /**
     * Route PUBLIQUE : Sauvegarde le service en session avant la connexion/inscription
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function prepare(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:catalogue_services,id',
        ]);

        session(['order_service_id' => $request->service_id]);

        return response()->json(['success' => true]);
    }

    /**
     * Étape 1 : Initialisation de la commande depuis le catalogue public.
     * Stocke l'identifiant du service en session avant la soumission.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:catalogue_services,id',
        ]);

        // On stocke le service en session pour commencer le wizard
        session(['order_service_id' => $request->service_id]);

        return redirect()->route('commande.step');
    }

    /**
     * Affiche le formulaire de commande (Wizard) avec le panier et les services.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function step(Request $request)
    {
        $cart = session('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('catalogue.index')->with('error', 'Votre panier est vide.');
        }

        $serviceIds = array_keys($cart);
        $services = CatalogueService::with('category')->whereIn('id', $serviceIds)->get();

        return view('app', [
            'page' => 'public-order-wizard',
            'props' => [
                'cart' => $cart,
                'services' => $services,
                'user' => Auth::check() ? Auth::user()->only('id', 'name', 'email', 'phone') : null,
            ]
        ]);
    }

    /**
     * Soumission finale de la commande.
     * Supporte le panier multi-services (session cart) et le service unique (session order_service_id).
     * Crée les commandes, enregistre l'historique des statuts et attache les documents.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        $request->merge([
            'form_data' => $request->input('form_data', []),
        ]);

        $request->validate([
            'form_data'      => 'nullable|array',
            'documents.*'    => 'nullable|file|max:10240',
            'payment_method' => 'nullable|string|in:MTN,MOOV',
            'phone_number'   => 'nullable|string|max:20',
        ]);

        $clientId = Auth::id();

        // Enrichir form_data avec le mode de paiement
        $formData = array_merge(
            $request->form_data ?? [],
            [
                'payment_method' => $request->payment_method,
                'phone_number'   => $request->phone_number,
            ]
        );

        // Déterminer les services à commander
        $cart = session('cart', []);
        $serviceId = session('order_service_id');

        if (!empty($cart)) {
            // Commande depuis le PANIER
            $serviceIds = array_keys($cart);
            $services = CatalogueService::whereIn('id', $serviceIds)->get()->keyBy('id');
            $ordersCreated = [];

            foreach ($cart as $svcId => $item) {
                $service = $services[$svcId] ?? null;
                if (!$service) continue;

                $reference = 'GS-' . date('Y') . '-' . strtoupper(Str::random(5));
                $order = CatalogueOrder::create([
                    'reference'           => $reference,
                    'client_id'           => $clientId,
                    'service_id'          => $service->id,
                    'categorie_id'        => $service->category_id,
                    'statut'              => 'Nouvelle Demande',
                    'date_commande'       => now(),
                    'delai_estime'        => $service->delai_jours,
                    'montant_estime_fcfa' => $service->tarif_type === 'fixe' ? $service->tarif_fcfa : null,
                    'form_data'           => $formData,
                ]);

                CatalogueOrderStatusHistory::create([
                    'commande_id'     => $order->id,
                    'statut_precedent'=> null,
                    'statut_nouveau'  => 'Nouvelle Demande',
                    'id_user'         => $clientId,
                    'commentaire'     => 'Création depuis le panier',
                ]);

                $ordersCreated[] = $order;
            }

            // Attacher les fichiers à la première commande seulement
            $firstOrder = $ordersCreated[0] ?? null;
            if ($firstOrder && $request->hasFile('documents')) {
                $types = $request->input('document_types', []);
                foreach ($request->file('documents') as $index => $file) {
                    $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('catalogue_documents/' . $firstOrder->id, $filename, 'public');
                    \App\Models\CatalogueOrderDocument::create([
                        'commande_id'     => $firstOrder->id,
                        'type'            => isset($types[$index]) ? $types[$index] : 'client_fourni',
                        'nom_fichier'     => $file->getClientOriginalName(),
                        'chemin_stockage' => $path,
                        'taille_ko'       => intval($file->getSize() / 1024),
                        'id_user'         => $clientId,
                    ]);
                }
            }

            session()->forget('cart');
            session()->forget('order_service_id');

            return response()->json([
                'success'  => true,
                'redirect' => route('client.orders.index'),
                'message'  => 'Votre commande a été soumise avec succès !',
            ]);

        } elseif ($serviceId) {
            // Commande SERVICE UNIQUE (ancien comportement)
            $service = CatalogueService::findOrFail($serviceId);

            $reference = 'GS-' . date('Y') . '-' . strtoupper(Str::random(5));
            $order = CatalogueOrder::create([
                'reference'           => $reference,
                'client_id'           => $clientId,
                'service_id'          => $service->id,
                'categorie_id'        => $service->category_id,
                'statut'              => 'Nouvelle Demande',
                'date_commande'       => now(),
                'delai_estime'        => $service->delai_jours,
                'montant_estime_fcfa' => $service->tarif_type === 'fixe' ? $service->tarif_fcfa : null,
                'form_data'           => $formData,
            ]);

            CatalogueOrderStatusHistory::create([
                'commande_id'      => $order->id,
                'statut_precedent' => null,
                'statut_nouveau'   => 'Nouvelle Demande',
                'id_user'          => $clientId,
                'commentaire'      => 'Création initiale par le client',
            ]);

            session()->forget('order_service_id');

            if ($request->hasFile('documents')) {
                $types = $request->input('document_types', []);
                foreach ($request->file('documents') as $index => $file) {
                    $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('catalogue_documents/' . $order->id, $filename, 'public');
                    \App\Models\CatalogueOrderDocument::create([
                        'commande_id'     => $order->id,
                        'type'            => isset($types[$index]) ? $types[$index] : 'client_fourni',
                        'nom_fichier'     => $file->getClientOriginalName(),
                        'chemin_stockage' => $path,
                        'taille_ko'       => intval($file->getSize() / 1024),
                        'id_user'         => $clientId,
                    ]);
                }
            }

            return response()->json([
                'success'  => true,
                'redirect' => route('client.orders.show', $order->id),
                'message'  => 'Votre demande a été soumise avec succès !',
            ]);
        }

        return redirect()->route('catalogue.index');
    }
}
