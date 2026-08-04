<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\DocumentSignature;
use App\Models\Document;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

/**
 * Contrôleur des signatures électroniques de documents.
 * Gère le cycle de vie des signatures : création d'une demande,
 * signature via lien public, visualisation et suppression.
 * Journalise chaque action via AuditTrailService.
 */
class DocumentSignatureController extends Controller
{
    /**
     * Liste paginée des signatures avec filtre optionnel par document.
     *
     * @param Request $request La requête HTTP avec le filtre optionnel document_id
     * @return View
     */
    public function index(Request $request): View
    {
        $query = DocumentSignature::with('document');
        if ($request->filled('document_id')) $query->where('document_id', $request->document_id);

        $signatures = $query->latest()->paginate(20);
        return view('app', ['page' => 'gel-document-signatures', 'props' => compact('signatures')]);
    }

    /**
     * Affiche le formulaire de création d'une demande de signature.
     * Liste les documents disponibles pour la sélection.
     *
     * @return View
     */
    public function create(): View
    {
        $documents = Document::orderBy('title')->get(['id', 'title']);
        return view('app', ['page' => 'gel-document-signatures-form', 'props' => compact('documents')]);
    }

    /**
     * Enregistre une nouvelle demande de signature.
     * Génère un hash du document et un token unique pour le lien de signature.
     * Journalise l'action dans AuditTrail.
     *
     * @param Request $request La requête HTTP avec les données de la demande
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_id' => 'required|exists:documents,id',
            'signer_name' => 'required|string|max:255',
            'signer_email' => 'nullable|email|max:255',
            'signer_phone' => 'nullable|string|max:20',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $document = Document::findOrFail($validated['document_id']);
        // Génération du hash et du token sécurisé pour le lien de signature
        $validated['document_hash'] = hash('sha256', $document->id . $document->title);
        $validated['token'] = Str::random(64);

        $signature = DocumentSignature::create($validated);
        AuditTrailService::log($signature, 'created', null, $validated, 'Signature électronique initiée');

        return redirect()->route('gel.document-signatures.index')
            ->with('success', 'Invitation à signer envoyée.');
    }

    /**
     * Affiche le détail d'une demande de signature avec son document.
     *
     * @param DocumentSignature $signature La signature à afficher
     * @return View
     */
    public function show(DocumentSignature $signature): View
    {
        $signature->load('document');
        return view('app', ['page' => 'gel-document-signatures-show', 'props' => compact('signature')]);
    }

    // ─── Signing link (public) ─────────────────────────────────

    /**
     * Affiche la page de signature publique via un token.
     * Vérifie que le token est valide et que la signature n'a pas déjà été soumise.
     *
     * @param string $token Le token unique de la demande de signature
     * @return View
     */
    public function signByToken(string $token): View
    {
        $signature = DocumentSignature::with('document')
            ->where('token', $token)
            ->whereNull('signature_data')
            ->firstOrFail();

        return view('app', ['page' => 'gel-document-sign', 'props' => compact('signature')]);
    }

    /**
     * Soumet la signature électronique via le token public.
     * Enregistre les données de signature, l'horodatage et l'adresse IP.
     *
     * @param Request $request La requête HTTP avec les données de signature
     * @param string $token Le token unique de la demande de signature
     * @return RedirectResponse
     */
    public function submitSignature(Request $request, string $token): RedirectResponse
    {
        $signature = DocumentSignature::with('document')
            ->where('token', $token)
            ->whereNull('signature_data')
            ->firstOrFail();

        $validated = $request->validate([
            'signature_data' => 'required|string',
        ]);

        $signature->update([
            'signature_data' => $validated['signature_data'],
            'signed_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('gel.document-signatures.show', $signature)
            ->with('success', 'Document signé avec succès.');
    }

    /**
     * Supprime une demande de signature.
     * Journalise la suppression dans AuditTrail.
     *
     * @param DocumentSignature $signature La signature à supprimer
     * @return RedirectResponse
     */
    public function destroy(DocumentSignature $signature): RedirectResponse
    {
        $old = $signature->getAttributes();
        $signature->delete();
        AuditTrailService::log($signature, 'deleted', $old, null, 'Signature supprimée');
        return redirect()->route('gel.document-signatures.index')->with('success', 'Signature supprimée.');
    }
}
