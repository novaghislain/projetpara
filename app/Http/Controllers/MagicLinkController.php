<?php

namespace App\Http\Controllers;

use App\Models\MagicLinkRequest;
use App\Services\MagicLinkService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MagicLinkController extends Controller
{
    protected MagicLinkService $magicLinkService;

    public function __construct(MagicLinkService $magicLinkService)
    {
        $this->magicLinkService = $magicLinkService;
    }

    /**
     * Affiche la vue publique pour qu'un client uploade ses documents.
     */
    public function showPublicUpload(string $uuid)
    {
        if (!$this->magicLinkService->isValid($uuid)) {
            return Inertia::render('Public/MagicLinkExpired');
        }

        $request = MagicLinkRequest::where('uuid', $uuid)->firstOrFail();

        return Inertia::render('Public/MagicLinkUpload', [
            'magicRequest' => $request,
        ]);
    }

    /**
     * Gère la soumission des fichiers par le client.
     */
    public function submitUpload(Request $request, string $uuid)
    {
        if (!$this->magicLinkService->isValid($uuid)) {
            return response()->json(['error' => 'Lien expiré ou invalide'], 403);
        }

        $request->validate([
            'files.*' => 'required|file|max:20480', // 20MB max par fichier
        ]);

        $uploadedPaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('magic_links/' . $uuid, 'public');
                $uploadedPaths[] = $path;
            }
        }

        $this->magicLinkService->processUpload($uuid, $uploadedPaths);

        return redirect()->back()->with('success', 'Vos documents ont été transmis avec succès.');
    }
}
