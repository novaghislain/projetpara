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
     * Route PUBLIQUE : Sauvegarde le service en session avant la connexion/inscription
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
     * Étape 1 : Initialisation de la commande depuis le catalogue public
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
     * Affiche le formulaire de commande (Wizard)
     */
    public function step(Request $request)
    {
        $serviceId = session('order_service_id');
        
        if (!$serviceId) {
            return redirect()->route('catalogue.index')->with('error', 'Veuillez sélectionner un service pour commencer.');
        }

        $service = CatalogueService::with('category')->findOrFail($serviceId);

        return view('app', [
            'page' => 'public-order-wizard',
            'props' => [
                'service' => $service,
                'user' => Auth::check() ? Auth::user()->only('id', 'name', 'email', 'phone') : null,
            ]
        ]);
    }

    /**
     * Soumission finale de la commande
     */
    public function submit(Request $request)
    {
        $serviceId = session('order_service_id');
        if (!$serviceId) {
            return redirect()->route('catalogue.index');
        }

        $service = CatalogueService::findOrFail($serviceId);

        $request->merge([
            'form_data' => $request->input('form_data', []),
        ]);

        $request->validate([
            'form_data'   => 'nullable|array',
            'documents.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        $clientId = Auth::id();

        // Génération de la référence unique
        $reference = 'GS-' . date('Y') . '-' . strtoupper(Str::random(5));

        // Création de la commande
        $order = CatalogueOrder::create([
            'reference' => $reference,
            'client_id' => $clientId,
            'service_id' => $service->id,
            'categorie_id' => $service->category_id,
            'statut' => 'Nouvelle Demande',
            'date_commande' => now(),
            'delai_estime' => $service->delai_jours,
            'montant_estime_fcfa' => $service->tarif_type === 'fixe' ? $service->tarif_fcfa : null,
            'form_data' => $request->form_data,
        ]);

        // Historique
        CatalogueOrderStatusHistory::create([
            'commande_id' => $order->id,
            'statut_precedent' => null,
            'statut_nouveau' => 'Nouvelle Demande',
            'id_user' => $clientId,
            'commentaire' => 'Création initiale par le client',
        ]);

        // Nettoyage session
        session()->forget('order_service_id');

        // Upload des documents joints par le client
        if ($request->hasFile('documents')) {
            $types = $request->input('document_types', []);
            foreach ($request->file('documents') as $index => $file) {
                $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('catalogue_documents/' . $order->id, $filename, 'public');

                $docType = isset($types[$index]) ? $types[$index] : 'client_fourni';

                \App\Models\CatalogueOrderDocument::create([
                    'commande_id'      => $order->id,
                    'type'             => $docType,
                    'nom_fichier'      => $file->getClientOriginalName(),
                    'chemin_stockage'  => $path,
                    'taille_ko'        => intval($file->getSize() / 1024),
                    'id_user'          => $clientId,
                ]);
            }
        }

        // Retour JSON pour la requête fetch du wizard
        return response()->json([
            'success'  => true,
            'redirect' => route('client.orders.show', $order->id),
            'message'  => 'Votre demande a été soumise avec succès !',
        ]);
    }
}
