<?php

namespace App\Services;

use App\Models\MagicLinkRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MagicLinkService
{
    /**
     * Crée une nouvelle demande de document via Magic Link
     */
    public function createRequest(string $clientId, string $title, array $requestedDocuments, int $expiresInDays = 7): MagicLinkRequest
    {
        return MagicLinkRequest::create([
            'uuid' => Str::uuid()->toString(),
            'client_id' => $clientId,
            'title' => $title,
            'requested_documents' => $requestedDocuments,
            'expires_at' => Carbon::now()->addDays($expiresInDays),
            'status' => 'pending',
        ]);
    }

    /**
     * Valide si le magic link est valide et non expiré
     */
    public function isValid(string $uuid): bool
    {
        $request = MagicLinkRequest::where('uuid', $uuid)->first();
        if (!$request) return false;
        
        return !$request->expires_at->isPast() && $request->status !== 'completed';
    }

    /**
     * Traite l'upload des fichiers clients (simulé)
     */
    public function processUpload(string $uuid, array $filesPaths): bool
    {
        $request = MagicLinkRequest::where('uuid', $uuid)->first();
        if (!$request || !$this->isValid($uuid)) return false;

        $existingFiles = $request->uploaded_files ?? [];
        $mergedFiles = array_merge($existingFiles, $filesPaths);

        $request->update([
            'uploaded_files' => $mergedFiles,
            'status' => 'completed',
        ]);

        return true;
    }
}
