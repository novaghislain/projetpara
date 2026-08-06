<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\License;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du tableau de bord Company.
 *
 * Gère les pages principales de l'interface entreprise :
 * tableau de bord, services, profil entreprise, et les API
 * de consultation/mise à jour des informations de l'entreprise.
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord principal de l'entreprise (vue SPA).
     *
     * Vérifie que l'utilisateur a une entreprise associée.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function dashboard()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }

        return view('company', [
            'page' => 'company-dashboard',
            'clientId' => $user->client_id,
        ]);
    }

    /**
     * Affiche la page des services souscrits (vue SPA).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function services()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }

        return view('company', [
            'page' => 'company-services',
            'clientId' => $user->client_id,
        ]);
    }

    /**
     * Affiche la page de profil entreprise (vue SPA).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function profile()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }

        return view('company', [
            'page' => 'company-profile',
            'clientId' => $user->client_id,
        ]);
    }

    /**
     * S1.4 — Historique commun de coordination consultable par l'Administrateur
     * d'Entreprise (lecture seule). L'admin ne voit que la coordination de SA
     * propre entreprise (client_id) — jamais celle d'une autre entreprise.
     */
    public function coordinationHistory()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }

        $events = \App\Models\Gel\CoordinationEvent::with(['actor:id,name,role'])
            ->where('client_id', $user->client_id)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $client = Client::find($user->client_id);

        // Vue lecture seule (aucun formulaire écrit)
        return view('company.coordination.history', compact('events', 'client'));
    }

    // ── API ──

    /**
     * Vérifie que l'utilisateur authentifié a bien accès à ce client.
     */
    /**
     * Vérifie que l'utilisateur authentifié a bien accès à ce client.
     *
     * @param int $clientId Identifiant du client à vérifier
     * @return void
     */
    private function authorizeClientAccess($clientId): void
    {
        $user = Auth::user();
        if ((int) $user->client_id !== (int) $clientId) {
            abort(403, 'Vous n\'avez pas accès aux données de cette entreprise.');
        }
    }

    /**
     * API: Retourne les informations de l'entreprise, licences et statistiques.
     *
     * @param int $clientId Identifiant du client
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyInfo($clientId)
    {
        $this->authorizeClientAccess($clientId);
        $client = Client::with(['services', 'activeLicenses', 'activeLicenses.service'])->findOrFail($clientId);

        $licenses = $client->activeLicenses()->with('service')->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'license_key' => $l->license_key,
                'service_name' => $l->service->name ?? 'N/A',
                'service_id' => $l->service_id,
                'duration_months' => $l->duration_months,
                'start_date' => $l->start_date?->format('d/m/Y'),
                'end_date' => $l->end_date?->format('d/m/Y'),
                'status' => $l->status,
                'valid' => $l->isValid(),
            ];
        });

        // Statistiques pour le dashboard
        $userCount = User::where('client_id', $clientId)->count();
        $activeUserCount = User::where('client_id', $clientId)->where('is_active', true)->count();

        return response()->json([
            'company' => $client,
            'licenses' => $licenses,
            'stats' => [
                'user_count' => $userCount,
                'active_user_count' => $activeUserCount,
            ],
        ]);
    }

    /**
     * API: Met à jour les informations de l'entreprise.
     *
     * @param Request $request Requête HTTP (company_name, email, phone, address)
     * @param int $clientId Identifiant du client
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCompany(Request $request, $clientId)
    {
        $this->authorizeClientAccess($clientId);
        $client = Client::findOrFail($clientId);
        
        $rules = [
            'company_name' => 'required|string|max:255',
            'legal_form' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:100',
            'ifu' => 'nullable|string|max:100',
            'secteur' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048', // 2MB max
        ];
        
        $data = $request->validate($rules);
        
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company_logos', 'public');
            $data['logo_path'] = $path;
            unset($data['logo']);
        }

        $client->update($data);
        
        return response()->json(['message' => 'Informations mises à jour.', 'company' => $client]);
    }

    /**
     * API: Récupère les documents légaux de l'entreprise.
     */
    public function getLegalDocuments($clientId)
    {
        $this->authorizeClientAccess($clientId);
        $documents = \App\Models\Document::where('client_id', $clientId)
            ->where('category', 'legal')
            ->with('uploadedBy:id,name')
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['documents' => $documents]);
    }

    /**
     * API: Téléverse un document légal.
     */
    public function uploadLegalDocument(Request $request, $clientId)
    {
        $this->authorizeClientAccess($clientId);
        $request->validate([
            'file' => 'required|file|max:10240',
            'name' => 'required|string|max:255',
        ]);
        
        $file = $request->file('file');
        $path = $file->store("clients/{$clientId}/legal", 'local');

        $doc = \App\Models\Document::create([
            'client_id' => $clientId,
            'category' => 'legal',
            'name' => $request->name,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Document téléversé avec succès.', 'document' => $doc->load('uploadedBy:id,name')]);
    }

    /**
     * API: Supprime un document légal.
     */
    public function deleteLegalDocument($clientId, $documentId)
    {
        $this->authorizeClientAccess($clientId);
        $doc = \App\Models\Document::where('client_id', $clientId)
            ->where('category', 'legal')
            ->findOrFail($documentId);
        $doc->delete();
        return response()->json(['message' => 'Document supprimé avec succès.']);
    }

    /**
     * API: Transfère la propriété de l'entreprise à un autre membre.
     */
    public function transferOwnership(Request $request, $clientId)
    {
        $this->authorizeClientAccess($clientId);
        
        $request->validate([
            'new_owner_id' => 'required|exists:users,id',
            'password' => 'required|current_password', // Sécurité supplémentaire
        ]);

        $newOwner = User::where('client_id', $clientId)->findOrFail($request->new_owner_id);
        $currentOwner = Auth::user();

        if ($newOwner->id === $currentOwner->id) {
            return response()->json(['message' => 'Vous êtes déjà le propriétaire.'], 400);
        }

        // Transférer le rôle
        $newOwner->is_company_admin = true;
        $newOwner->save();

        // Le propriétaire actuel perd ses droits de super-admin entreprise (optionnel selon règle métier)
        // $currentOwner->is_company_admin = false;
        // $currentOwner->save();
        
        $client = Client::findOrFail($clientId);
        $client->created_by = $newOwner->id;
        $client->save();

        // Tracabilité
        if (class_exists(\App\Models\Gel\GelAuditLog::class)) {
            \App\Models\Gel\GelAuditLog::create([
                'user_id' => $currentOwner->id,
                'client_id' => $clientId,
                'action' => 'Transfert de propriété',
                'description' => "Propriété transférée à {$newOwner->name} ({$newOwner->email})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json(['message' => 'Propriété transférée avec succès.']);
    }

    /**
     * API: Résumé transversal des activités récentes sur tous les portails.
     * Section 5: Portails et Vue d'Ensemble
     */
    public function getCrossPortalStats()
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $stats = [];

        // 1. Tâches Secrétariat (dae_taches)
        if (class_exists(\App\Models\Dae\DaeTache::class)) {
            $pendingTasks = \App\Models\Dae\DaeTache::where('client_id', $clientId)
                ->where('statut', 'à faire')
                ->count();
            $stats[] = [
                'portal' => 'Secrétariat',
                'icon' => 'bi-list-task',
                'label' => "$pendingTasks tâche(s) en attente",
                'count' => $pendingTasks,
                'link' => '/dae/taches'
            ];
        }

        // 2. Factures Impayées (Compta)
        if (class_exists(\App\Models\Invoice::class)) {
            $unpaidInvoices = \App\Models\Invoice::where('client_id', $clientId)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->count();
            $stats[] = [
                'portal' => 'Comptabilité',
                'icon' => 'bi-receipt',
                'label' => "$unpaidInvoices facture(s) impayée(s)",
                'count' => $unpaidInvoices,
                'link' => '/compta/factures'
            ];
        }

        // 3. Documents à valider (GED)
        if (class_exists(\App\Models\Document::class)) {
            $pendingDocs = \App\Models\Document::where('client_id', $clientId)
                ->where('status', 'pending_validation')
                ->count();
            $stats[] = [
                'portal' => 'GED',
                'icon' => 'bi-file-earmark-check',
                'label' => "$pendingDocs document(s) à valider",
                'count' => $pendingDocs,
                'link' => '/ged/validation'
            ];
        }

        // 4. Demandes Clients B2C
        if (class_exists(\App\Models\ClientRequest::class)) {
            $pendingRequests = \App\Models\ClientRequest::where('client_id', $clientId)
                ->where('status', 'pending')
                ->count();
            $stats[] = [
                'portal' => 'Demandes Clients',
                'icon' => 'bi-envelope',
                'label' => "$pendingRequests demande(s) client B2C",
                'count' => $pendingRequests,
                'link' => '/company/requests'
            ];
        }

        return response()->json([
            'stats' => $stats
        ]);
    }
}
