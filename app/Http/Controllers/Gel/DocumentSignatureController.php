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

class DocumentSignatureController extends Controller
{
    public function index(Request $request): View
    {
        $query = DocumentSignature::with('document');
        if ($request->filled('document_id')) $query->where('document_id', $request->document_id);

        $signatures = $query->latest()->paginate(20);
        return view('app', ['page' => 'gel-document-signatures', 'props' => compact('signatures')]);
    }

    public function create(): View
    {
        $documents = Document::orderBy('title')->get(['id', 'title']);
        return view('app', ['page' => 'gel-document-signatures-form', 'props' => compact('documents')]);
    }

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
        $validated['document_hash'] = hash('sha256', $document->id . $document->title);
        $validated['token'] = Str::random(64);

        $signature = DocumentSignature::create($validated);
        AuditTrailService::log($signature, 'created', null, $validated, 'Signature électronique initiée');

        return redirect()->route('gel.document-signatures.index')
            ->with('success', 'Invitation à signer envoyée.');
    }

    public function show(DocumentSignature $signature): View
    {
        $signature->load('document');
        return view('app', ['page' => 'gel-document-signatures-show', 'props' => compact('signature')]);
    }

    // ─── Signing link (public) ─────────────────────────────────
    public function signByToken(string $token): View
    {
        $signature = DocumentSignature::with('document')
            ->where('token', $token)
            ->whereNull('signature_data')
            ->firstOrFail();

        return view('app', ['page' => 'gel-document-sign', 'props' => compact('signature')]);
    }

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

    public function destroy(DocumentSignature $signature): RedirectResponse
    {
        $old = $signature->getAttributes();
        $signature->delete();
        AuditTrailService::log($signature, 'deleted', $old, null, 'Signature supprimée');
        return redirect()->route('gel.document-signatures.index')->with('success', 'Signature supprimée.');
    }
}
