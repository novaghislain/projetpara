<?php

namespace App\Http\Controllers\GelAccountant\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Courrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the documents.
     */
    public function index(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');

        $query = Document::where('client_id', $clientId)
            ->whereNull('deleted_at');

        if ($request->has('categorie') && $request->categorie != '') {
            $query->where('category', $request->categorie);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('gel-accountant.secretariat.documents.index', compact('documents'));
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'courrier_id' => 'nullable|uuid|exists:courriers,id'
        ]);

        $file = $request->file('file');
        
        $fileName = $file->getClientOriginalName();
        $filePath = $file->storeAs('documents/' . $clientId, Str::uuid() . '_' . $fileName, 'local');
        
        $document = new Document();
        $document->client_id = $clientId;
        $document->name = pathinfo($fileName, PATHINFO_FILENAME);
        $document->original_name = $fileName;
        $document->file_path = $filePath;
        $document->file_size = $file->getSize();
        $document->mime_type = $file->getMimeType();
        $document->category = $request->category;
        $document->description = $request->description;
        $document->uploaded_by = auth()->id();
        $document->save();

        // Rattachement au courrier si spécifié
        if ($request->has('courrier_id') && $request->courrier_id) {
            $courrier = Courrier::find($request->courrier_id);
            if ($courrier && $courrier->client_id == $clientId) {
                $courrier->documents()->attach($document->id);
                return redirect()->route('gel-accountant.secretariat.courriers.show', $courrier->id)
                    ->with('success', 'Document importé et rattaché avec succès.');
            }
        }

        return redirect()->route('gel-accountant.secretariat.documents.index')
            ->with('success', 'Document importé avec succès.');
    }

    /**
     * Download the specified document.
     */
    public function download($id)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $document = Document::where('client_id', $clientId)->findOrFail($id);
        
        if (Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download($document->file_path, $document->original_name);
        }
        
        return back()->with('error', 'Le fichier physique est introuvable.');
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $document = Document::where('client_id', $clientId)->findOrFail($id);
        
        // Soft delete the document record
        $document->delete();
        
        return back()->with('success', 'Document supprimé avec succès.');
    }

    /**
     * Attach an existing document to a courrier.
     */
    public function attachToCourrier(Request $request, $courrierId)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $request->validate([
            'document_id' => 'required|exists:documents,id'
        ]);

        $courrier = Courrier::where('client_id', $clientId)->findOrFail($courrierId);
        $document = Document::where('client_id', $clientId)->findOrFail($request->document_id);

        $courrier->documents()->syncWithoutDetaching([$document->id]);

        return back()->with('success', 'Document rattaché au courrier.');
    }
    
    /**
     * Detach a document from a courrier.
     */
    public function detachFromCourrier($courrierId, $documentId)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $courrier = Courrier::where('client_id', $clientId)->findOrFail($courrierId);
        $courrier->documents()->detach($documentId);

        return back()->with('success', 'Document détaché du courrier.');
    }
}
