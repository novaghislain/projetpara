<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Exception;

class PublicDepositController extends Controller
{
    /**
     * Génère un token sécurisé pour un client donné.
     * Cette méthode peut être appelée statiquement depuis une vue.
     */
    public static function generateToken($cabinetId, $clientId)
    {
        $payload = json_encode([
            'cabinet_id' => $cabinetId,
            'client_id' => $clientId,
        ]);
        // Utilisation de base64url_encode et hmac pour signer le token
        $data = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $data, config('app.key'));
        return $data . '.' . $signature;
    }

    /**
     * Vérifie et décode un token. Retourne le payload (cabinet_id, client_id) ou false.
     */
    private function verifyToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) return false;

        $data = $parts[0];
        $signature = $parts[1];

        $expectedSignature = hash_hmac('sha256', $data, config('app.key'));
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $data)), true);
        if (!$payload || !isset($payload['cabinet_id']) || !isset($payload['client_id'])) {
            return false;
        }

        return $payload;
    }

    /**
     * Affiche le formulaire de dépôt public.
     */
    public function show($token)
    {
        $payload = $this->verifyToken($token);
        if (!$payload) {
            abort(403, 'Lien invalide ou expiré.');
        }

        $client = Client::where('id', $payload['client_id'])->where('cabinet_id', $payload['cabinet_id'])->firstOrFail();

        return view('public.deposit.index', compact('client', 'token'));
    }

    /**
     * Traite l'upload de document.
     */
    public function upload(Request $request, $token)
    {
        $payload = $this->verifyToken($token);
        if (!$payload) {
            abort(403, 'Lien invalide ou expiré.');
        }

        $client = Client::where('id', $payload['client_id'])->where('cabinet_id', $payload['cabinet_id'])->firstOrFail();

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:500'
        ]);

        $file = $request->file('file');
        
        // Structure de dossier: documents/{client_id}/...
        $path = $file->store("documents/{$client->id}", 'public');

        Document::create([
            'cabinet_id' => $client->cabinet_id,
            'client_id' => $client->id,
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->extension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'folder_id' => null,
            // uploader system
        ]);

        return redirect()->route('public.deposit.success');
    }

    public function success()
    {
        return view('public.deposit.success');
    }
}
