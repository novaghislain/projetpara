<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentSignature;
use App\Models\Document;
use Illuminate\Support\Str;

class SignatureController extends Controller
{
    /**
     * Générer un lien de signature pour un client
     */
    public function generateLink(Request $request, $documentId)
    {
        $document = Document::findOrFail($documentId);
        
        $signature = DocumentSignature::create([
            'document_id' => $document->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
            'document_hash' => hash_file('sha256', storage_path('app/' . $document->file_path)),
        ]);

        return response()->json([
            'url' => route('signature.show', $signature->token)
        ]);
    }

    /**
     * Afficher l'interface de signature au client
     */
    public function show($token)
    {
        $signature = DocumentSignature::where('token', $token)->firstOrFail();

        if ($signature->signed_at || $signature->expires_at < now()) {
            abort(403, 'Ce lien est expiré ou le document a déjà été signé.');
        }

        return view('client.signature', compact('signature'));
    }

    /**
     * Traiter la signature
     */
    public function process(Request $request, $token)
    {
        $request->validate([
            'signature_data' => 'required|string',
            'signer_name' => 'required|string',
        ]);

        $signature = DocumentSignature::where('token', $token)->firstOrFail();

        if ($signature->signed_at) {
            return response()->json(['message' => 'Déjà signé'], 400);
        }

        $signature->update([
            'signer_name' => $request->signer_name,
            'signature_data' => $request->signature_data, // Base64 PNG
            'ip_address' => $request->ip(),
            'signed_at' => now(),
        ]);

        return response()->json(['message' => 'Document signé avec succès']);
    }
}
