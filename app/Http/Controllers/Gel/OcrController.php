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
    public function __construct(
        private OcrService $ocrService
    ) {}

    public function index(Request $request): View
    {
        $query = DocumentScan::with('client')->latest();

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

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-ocr-form', 'props' => compact('clients')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'document' => 'required|file|mimes:pdf,png,jpg,jpeg,tiff|max:20480',
            'document_id' => 'nullable|exists:documents,id',
        ]);

        $file = $request->file('document');
        $path = $file->store('ocr/' . $validated['client_id'], 'public');

        $scan = $this->ocrService->analyzeAndSave(
            $path,
            $file->getMimeType(),
            $validated['client_id'],
            $validated['document_id'] ?? null,
            ['filename' => $file->getClientOriginalName()]
        );

        AuditTrailService::log($scan, 'created', null, [
            'client_id' => $validated['client_id'],
            'filename' => $file->getClientOriginalName(),
        ], 'Document scanné (OCR)');

        return redirect()->route('gel.ocr.show', $scan)
            ->with('success', 'Document analysé avec succès.');
    }

    public function show(DocumentScan $scan): View
    {
        $scan->load('client', 'document');
        return view('app', ['page' => 'gel-ocr-show', 'props' => compact('scan')]);
    }

    public function destroy(DocumentScan $scan): RedirectResponse
    {
        $scan->delete();
        AuditTrailService::log($scan, 'deleted', null, null, 'Scan OCR supprimé');
        return redirect()->route('gel.ocr.index')->with('success', 'Scan supprimé.');
    }
}
