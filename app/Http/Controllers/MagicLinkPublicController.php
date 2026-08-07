<?php

namespace App\Http\Controllers;

use App\Models\MagicLinkRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MagicLinkPublicController extends Controller
{
    /**
     * Page publique (sans auth) pour uploader les documents demandés.
     */
    public function show(string $token)
    {
        $link = MagicLinkRequest::where('token', $token)->firstOrFail();

        if ($link->isExpired()) {
            $link->update(['status' => 'expired']);
            return view('magic-link.expired', compact('link'));
        }

        // Marquer comme vu si c'est la première visite
        if ($link->status === 'sent') {
            $link->update(['status' => 'viewed', 'viewed_at' => now()]);
        }

        return view('magic-link.public', compact('link'));
    }

    /**
     * Réception des fichiers uploadés par le client.
     */
    public function upload(Request $request, string $token)
    {
        $link = MagicLinkRequest::where('token', $token)->firstOrFail();

        if ($link->isExpired() || $link->status === 'responded') {
            return back()->with('error', 'Ce lien n\'est plus valide.');
        }

        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,xlsx,xls,doc,docx',
            'message' => 'nullable|string|max:2000',
        ]);

        $paths = [];
        foreach ($request->file('files') as $file) {
            $paths[] = $file->store("magic-links/{$token}", 'public');
        }

        $link->update([
            'status'           => 'responded',
            'responded_at'     => now(),
            'response_files'   => $paths,
            'response_message' => $request->message,
        ]);

        return redirect()->route('magic-link.public', $token)
            ->with('success', 'Vos documents ont été envoyés avec succès ! Merci.');
    }
}
