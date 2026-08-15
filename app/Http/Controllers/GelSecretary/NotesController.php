<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GelNote;
use App\Models\Gel\Client;
use Illuminate\Support\Facades\Auth;

class NotesController extends Controller
{
    /**
     * Affiche l'éditeur de notes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        
        $query = GelNote::where('user_id', $user->id);
        if ($activeClientId) {
            $query->where('client_id', $activeClientId);
        }
        
        $notes = $query->orderBy('updated_at', 'desc')->get();
        
        // Charger la note sélectionnée si ID fourni, sinon la plus récente
        $currentNoteId = $request->query('note_id');
        $currentNote = null;
        
        if ($currentNoteId) {
            $currentNote = $notes->firstWhere('id', $currentNoteId);
        } else if ($notes->count() > 0) {
            $currentNote = $notes->first();
        }
        
        return view('gel-secretary.notes.index', compact('notes', 'currentNote', 'activeClientId'));
    }

    /**
     * Enregistre ou met à jour une note (via Ajax)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'note_id' => 'nullable|exists:gel_notes,id',
            'titre' => 'required|string|max:255',
            'contenu' => 'nullable|string',
        ]);
        
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;

        if ($request->note_id) {
            $note = GelNote::where('id', $request->note_id)
                           ->where('user_id', $user->id)
                           ->firstOrFail();
            $note->update([
                'titre' => $request->titre,
                'contenu' => $request->contenu,
                'client_id' => $activeClientId
            ]);
        } else {
            $note = GelNote::create([
                'user_id' => $user->id,
                'client_id' => $activeClientId,
                'titre' => $request->titre,
                'contenu' => $request->contenu,
                'statut' => 'brouillon'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'note' => $note
        ]);
    }
    
    /**
     * Supprime une note
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $note = GelNote::where('id', $id)
                       ->where('user_id', $user->id)
                       ->firstOrFail();
                       
        $note->delete();
        
        return redirect()->route('gel-secretary.notes.index')->with('success', 'Note supprimée avec succès.');
    }
}
