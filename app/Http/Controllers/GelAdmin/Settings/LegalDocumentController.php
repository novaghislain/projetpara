<?php

namespace App\Http\Controllers\GelAdmin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\GelAdmin\CabinetDocument;

class LegalDocumentController extends Controller
{
    use \App\Http\Controllers\GelAdmin\Traits\HasAdminEntity;

    public function index()
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        if ($type === 'cabinet') {
            $documents = CabinetDocument::where('cabinet_id', $cabinet->id)->get();
        } else {
            $documents = collect([]);
        }
        
        return view('gel-admin.settings.documents', compact('cabinet', 'documents'));
    }

    public function store(Request $request)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();

        $request->validate([
            'type' => 'required|string|max:100',
            'titre' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $path = $request->file('document')->store('cabinets/documents', 'public');

        if ($type === 'cabinet') {
            CabinetDocument::create([
                'cabinet_id' => $cabinet->id,
                'type' => $request->type,
                'titre' => $request->titre,
                'chemin' => $path,
            ]);
        }

        return back()->with('success', 'Document ajouté avec succès.');
    }

    public function destroy($id)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        if ($type === 'cabinet') {
            $document = CabinetDocument::where('cabinet_id', $cabinet->id)->findOrFail($id);

            Storage::disk('public')->delete($document->chemin);
            $document->delete();
        }

        return back()->with('success', 'Document supprimé.');
    }
}
