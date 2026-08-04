<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\DocumentScan;
use App\Models\Client;
use App\Services\OcrService;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur d'analyse OCR de documents.
 */
class OcrController extends Controller
{
    /**
     * Contrôleur d'analyse OCR (Reconnaissance Optique de Caractères) de documents.
     * Permet de soumettre des documents à l'analyse OCR, de consulter
     * les résultats et de gérer l'historique des scans.
     */

    public function __construct(
        private OcrService $ocrService
    ) {}

    /**
     * Liste paginée des analyses OCR avec filtres.
     *
     * @param Request $request La requête HTTP avec les filtres (client, statut)
     * @return View
     */
    public function index(Request $request): View
    {
        $query = DocumentScan::with('client')->latest();

        // Filtres optionnels
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $scans = $query->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);

        return view('app', [
            'page' => 'gel-ocr',
            'props' => compact('scans', 'clients'),
        ]);
    }

    /**
     * Affiche le formulaire de soumission d'un document pour analyse OCR.
     *
     * @return View
     */
    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-ocr-form', 'props' => compact('clients')]);
    }

    /**
     * Soumet un document pour analyse OCR et sauvegarde les résultats.
     *
     * @param Request $request La requête HTTP avec le fichier et les métadonnées
     * @return RedirectResponse Redirection vers le résultat du scan
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'document' => 'required|file|mimes:pdf,png,jpg,jpeg,tiff|max:20480',
            'document_id' => 'nullable|exists:documents,id',
        ]);

        // Stockage du fichier dans le répertoire dédié au client
        $file = $request->file('document');
        $path = $file->store('ocr/' . $validated['client_id'], 'public');

        // Analyse OCR via le service dédié
        $scan = $this->ocrService->analyzeAndSave(
            $path,
            $file->getMimeType(),
            $validated['client_id'],
            $validated['document_id'] ?? null,
            ['filename' => $file->getClientOriginalName()]
        );

        // Traçage de l'action dans l'audit
        AuditTrailService::log($scan, 'created', null, [
            'client_id' => $validated['client_id'],
            'filename' => $file->getClientOriginalName(),
        ], 'Document scanné (OCR)');

        return redirect()->route('gel.ocr.show', $scan)
            ->with('success', 'Document analysé avec succès.');
    }

    /**
     * Affiche le résultat d'une analyse OCR.
     *
     * @param DocumentScan $scan Le scan à afficher (injection de modèle)
     * @return View
     */
    public function show(DocumentScan $scan): View
    {
        $scan->load('client', 'document');
        return view('app', ['page' => 'gel-ocr-show', 'props' => compact('scan')]);
    }

    /**
     * Supprime un scan OCR.
     *
     * @param DocumentScan $scan Le scan à supprimer (injection de modèle)
     * @return RedirectResponse Redirection vers la liste
     */
    public function destroy(DocumentScan $scan): RedirectResponse
    {
        $scan->delete();
        AuditTrailService::log($scan, 'deleted', null, null, 'Scan OCR supprimé');
        return redirect()->route('gel.ocr.index')->with('success', 'Scan supprimé.');
    }
}
